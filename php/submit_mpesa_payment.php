<?php
// Include the database connection file
include __DIR__ . '/db_connect.php';

// MPESA API Credentials
$consumerKey = 'dYk4B9uVi0zGXv6t14zWNGJAGdd3Iua5uvGaVjpub58LZ5j8'; 
$consumerSecret = '4z4rDZrIBMLAJQPa3vnRRZHyBXl2VjeCdRJu34IRc7bMVSTnOXiQn0XZdWLVovPQ';
$shortcode = '600978';
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; 

// Generate Access Token
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);
$tokenUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$accessToken = $data['access_token'];

// Process Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);

    $customerName = $input['customerName'];
    $amount = $input['amount'];
    $phoneNumber = $input['paymentDetails']['phoneNumber'];

    $phoneNumber = '254' . substr($phoneNumber, -9);

    // Generate Timestamp
    $timestamp = date('YmdHis');

    // Generate Password
    $password = base64_encode($shortcode . $passkey . $timestamp);

    // STK Push Request
    $stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $callbackUrl = 'https://abc123.ngrok.io/callback.php'; 

    $stkData = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'TailorSuite',
        'TransactionDesc' => 'Payment for TailorSuite Services',
    ];

    $ch = curl_init($stkUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
        // Payment request successful
        $merchantRequestID = $result['MerchantRequestID'];
        $checkoutRequestID = $result['CheckoutRequestID'];

        // Save payment details to the database
        $sql = "INSERT INTO payments (customer_name, amount, payment_method, payment_details, transaction_id)
                VALUES (?, ?, 'mpesa', ?, ?)";
        $stmt = $conn->prepare($sql);
        $paymentDetails = json_encode(['phoneNumber' => $phoneNumber]);
        $stmt->bind_param("sdss", $customerName, $amount, $paymentDetails, $checkoutRequestID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Payment request sent successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving payment details.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment request failed.']);
    }
}
?>