<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "tailorsuite"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Fetch count of unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE is_read = 0";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'unread_count' => $row['unread_count']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error fetching unread notifications count.']);
}

$conn->close();
?>