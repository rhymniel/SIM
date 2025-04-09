<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Add your MySQL password if necessary
$dbname = "user_db"; // Ensure this matches your existing database name
$port = 3307; // Adjust if your MySQL uses a different port

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection Failed: " . htmlspecialchars($conn->connect_error));
}

// Validate and process input data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $account_id = htmlspecialchars(trim($_POST['account_id'])); // Clean input
    $password = trim($_POST['psw']);

    if (!empty($account_id) && !empty($password)) {
        // Prepare the SQL query to retrieve user data
        $stmt = $conn->prepare("SELECT password, name, role FROM users WHERE account_id = ?");
        $stmt->bind_param("s", $account_id);
        $stmt->execute();
        $stmt->store_result();

        // Check if the account exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password, $name, $role);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Successful login
                echo "Login Successful! Welcome, " . htmlspecialchars($name) . " (" . htmlspecialchars($role) . ").";
            } else {
                // Incorrect password
                echo "Error: Invalid password.";
            }
        } else {
            // Account ID not found
            echo "Error: Account ID not found.";
        }
        $stmt->close();
    } else {
        echo "Error: All fields are required.";
    }
}

$conn->close();
?>
