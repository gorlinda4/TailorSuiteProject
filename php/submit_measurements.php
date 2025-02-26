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
$customerName = $_POST['customerName'];
$customerId = $_POST['customerId'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$burst = $_POST['burst'];
$waist = $_POST['waist'];
$length = $_POST['length'];
$hip = $_POST['hip'];
$thigh = $_POST['thigh'];
$inseam = $_POST['inseam'];
$sleeve = $_POST['sleeve'];
$shoulder = $_POST['shoulder'];

// Validate input data
if (empty($customerName) || empty($customerId) || empty($email) || empty($phone) ||
    empty($burst) || empty($waist) || empty($length) || empty($hip) || empty($thigh) ||
    empty($inseam) || empty($sleeve) || empty($shoulder)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number.']);
    exit;
}

// Insert data into the database
$sql = "INSERT INTO measurements (customer_name, customer_id, email, phone, burst, waist, length, hip, thigh, inseam, sleeve, shoulder)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssddddddd", $customerName, $customerId, $email, $phone, $burst, $waist, $length, $hip, $thigh, $inseam, $sleeve, $shoulder);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Measurements submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting measurements: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>