<?php
// submit_order.php
$servername = "localhost";
$username = "root"; // use your database username
$password = ""; // use your database password
$dbname = "tailorsuite"; // database name

$conn = new mysqli($servername, $username, $password, $dbname);
include '/TailorSuiteProject/php/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerName = $_POST['customerName'];
    $fabric = $_POST['fabric'];

    $sql = "INSERT INTO orders (customer_name, fabric) VALUES ('$customerName', '$fabric')";

    if ($conn->query($sql) === TRUE) {
        header('Location: orders.html?success=Order placed successfully');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>