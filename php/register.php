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
$username = $_POST['username'];
$password = $_POST['password'];
$role = 'customer'; // Default role for registration

// Validate input data
if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required.']);
    exit;
}

// Hash the password if provided
$hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : NULL;

// Insert user into the database
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sss", $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error registering user: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>