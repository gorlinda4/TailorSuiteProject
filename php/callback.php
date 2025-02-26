<?php
header('Content-Type: application/json');

// Include the database connection file
include __DIR__ . '/db_connect.php';

// Get the raw POST data
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData, true);

// Log the callback data (for debugging)
file_put_contents('mpesa_callback.log', print_r($data, true));

if (isset($data['Body']['stkCallback']['ResultCode']) && $data['Body']['stkCallback']['ResultCode'] == 0) {
    // Payment was successful
    $transactionId = $data['Body']['stkCallback']['CheckoutRequestID'];
    $amount = $data['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
    $phoneNumber = $data['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

    // Update the database
    $sql = "UPDATE payments SET payment_status = 'completed' WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $transactionId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment received successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating payment status.']);
    }
} else {
    // Payment failed
    echo json_encode(['success' => false, 'message' => 'Payment failed.']);
}
?>