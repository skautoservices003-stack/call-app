<?php
// Updated save_call.php file with parameterized queries to prevent SQL injection

// Database connection
$host = 'localhost';
$user = 'username';
$password = 'password';
$dbname = 'database_name';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use prepared statements to prevent SQL injection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $call_id = $_POST['call_id'];
    $customer_name = $_POST['customer_name'];
    $call_details = $_POST['call_details'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO calls (call_id, customer_name, call_details) VALUES (?, ?, ?);");
    $stmt->bind_param("sss", $call_id, $customer_name, $call_details);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
}
$conn->close();
?>
