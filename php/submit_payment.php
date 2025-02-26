<?php
header('Content-Type: application/json');

// Include the database connection file
include __DIR__ . '/db_connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$customerName = $data['customerName'];
$amount = $data['amount'];
$paymentMethod = $data['paymentMethod'];
$paymentDetails = $data['paymentDetails'];
$transactionId = $data['transactionId'] ?? null;

// Save payment to database
$paymentDetailsJson = json_encode($paymentDetails);

$stmt = $conn->prepare("INSERT INTO payments (customer_name, amount, payment_method, payment_details, transaction_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sdsss", $customerName, $amount, $paymentMethod, $paymentDetailsJson, $transactionId);

if ($stmt->execute()) {
    if ($paymentMethod === 'mpesa') {
        // Initiate MPESA STK Push
        $response = initiateSTKPush($customerName, $amount, $paymentDetails['phoneNumber']);
        echo json_encode(['success' => true, 'message' => 'Payment request sent successfully. Please check your phone to complete the payment.', 'mpesa_response' => $response]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error processing payment.']);
}

$stmt->close();
$conn->close();

// Function to initiate MPESA STK Push
function initiateSTKPush($customerName, $amount, $phoneNumber) {
    $consumerKey = 'dYk4B9uVi0zGXv6t14zWNGJAGdd3Iua5uvGaVjpub58LZ5j8';
    $consumerSecret = '4z4rDZrIBMLAJQPa3vnRRZHyBXl2VjeCdRJu34IRc7bMVSTnOXiQn0XZdWLVovPQ';
    $shortcode = '600978';
    $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    $callbackUrl = 'https://abc123.ngrok.io/callback.php'; // Replace with your callback URL

    // Generate access token
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
    $tokenUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'];

    // Prepare STK Push request
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);

    $stkPushUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $stkPushData = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => $customerName,
        'TransactionDesc' => 'Payment for TailorSuite Services',
    ];

    $ch = curl_init($stkPushUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPushData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
?>