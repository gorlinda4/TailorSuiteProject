<?php
// submit_feedback.php
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
    $review = $_POST['review'];

    $sql = "INSERT INTO feedback (customer_name, review) VALUES ('$customerName', '$review')";

    if ($conn->query($sql) === TRUE) {
        header('Location: feedback.html?success=Feedback submitted successfully');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>