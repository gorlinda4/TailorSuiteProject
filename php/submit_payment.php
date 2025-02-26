<?php
// submit_payment.php
$servername = "localhost";
$username = "root"; // use your database username
$password = ""; // use your database password
$dbname = "tailoring"; // database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerName = $_POST['customerName'];
    $amount = $_POST['amount'];
    $paymentMethod = $_POST['paymentMethod'];

    $sql = "INSERT INTO payments (customer_name, amount, payment_method) VALUES ('$customerName', '$amount', '$paymentMethod')";

    if ($conn->query($sql) === TRUE) {
        header('Location: payments.html?success=Payment processed successfully');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>