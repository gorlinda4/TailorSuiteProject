<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "tailorsuite"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get form data
$customerName = $_POST['customerName'];
$review = $_POST['review'];

// Validate input data
if (empty($customerName) || empty($review)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Insert feedback into the database
$sql = "INSERT INTO feedback (customer_name, review) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $customerName, $review);

if ($stmt->execute()) {
    // Add a notification
    $notificationType = 'feedback';
    $notificationMessage = "New feedback submitted by $customerName.";
    $notificationSql = "INSERT INTO notifications (type, message) VALUES (?, ?)";
    $notificationStmt = $conn->prepare($notificationSql);
    $notificationStmt->bind_param("ss", $notificationType, $notificationMessage);
    $notificationStmt->execute();
    $notificationStmt->close();

    echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting feedback: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>