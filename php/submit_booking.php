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

// Get form data
$clientName = $_POST['clientName'];
$appointmentDate = $_POST['appointmentDate'];

// Validate input data
if (empty($clientName) || empty($appointmentDate)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Insert data into the database
$sql = "INSERT INTO appointments (client_name, appointment_date) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $clientName, $appointmentDate);

if ($stmt->execute()) {
    // Add a notification
    $notificationType = 'appointment';
    $notificationMessage = "New appointment booked by $clientName on $appointmentDate.";
    $notificationSql = "INSERT INTO notifications (type, message) VALUES (?, ?)";
    $notificationStmt = $conn->prepare($notificationSql);
    $notificationStmt->bind_param("ss", $notificationType, $notificationMessage);
    $notificationStmt->execute();
    $notificationStmt->close();

    echo json_encode(['success' => true, 'message' => 'Appointment booked successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error booking appointment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>