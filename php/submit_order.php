<?php
// Include the database connection file
include __DIR__ . '/db_connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize inputs
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $fabric_type = htmlspecialchars($_POST['fabric']);
    $design_type = htmlspecialchars($_POST['design']);
    $measurements = htmlspecialchars($_POST['measurements']);
    $delivery_option = htmlspecialchars($_POST['delivery']);

    // Validate inputs (basic validation)
    if (empty($name) || empty($email) || empty($phone) || empty($fabric_type) || empty($design_type) || empty($measurements) || empty($delivery_option)) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Prepare the SQL query to insert data into the `orders` table
    $sql = "INSERT INTO orders (name, email, phone, fabric_type, design_type, measurements, delivery_option)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param("sssssss", $name, $email, $phone, $fabric_type, $design_type, $measurements, $delivery_option);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Order placed successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If the form is not submitted, show an error
    die("Invalid request method.");
}
?>