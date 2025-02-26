<?php
session_start();
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
$username = $_POST['username'];
$password = $_POST['password'];

// Validate input data (only username is required)
if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required.']);
    exit;
}

// Fetch user from the database
$sql = "SELECT id, username, password, role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Check if password is provided
    if (empty($password)) {
        // Allow login without password
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        echo json_encode(['success' => true, 'message' => 'Login successful.']);
    } else {
        // Verify password if provided
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            echo json_encode(['success' => true, 'message' => 'Login successful.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username.']);
}

$stmt->close();
$conn->close();
?>