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
    die("Connection Failed: " . $conn->connect_error);
}

// Validate and process input data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars($_POST['name']); // Full name
    $username = htmlspecialchars($_POST['uname']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['psw'], PASSWORD_DEFAULT);
    $role = htmlspecialchars($_POST['role']);
    $keyword = htmlspecialchars($_POST['keyword']); // Role-specific keyword

    // Validate domain-specific emails for admin and teacher roles
    if ($role === 'admin' && !str_ends_with($email, '@principal.bcp.ph')) {
        die("Invalid email domain for admin. Admin emails must end with '@principal.bcp.ph'.");
    }
    if ($role === 'teacher' && !str_ends_with($email, '@educator.bcp.ph')) {
        die("Invalid email domain for teacher. Teacher emails must end with '@educator.bcp.ph'.");
    }
    if ($role === 'student' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format for student.");
    }

    // Validate keywords for admin and teacher roles
    if ($role === 'admin' && $keyword !== 'prin@123') {
        die("Invalid admin keyword. Signup denied.");
    }
    if ($role === 'teacher' && $keyword !== 'educ@123') {
        die("Invalid teacher keyword. Signup denied.");
    }

    // Generate account_id based on role
    $prefix = ($role === 'admin') ? 'a' : (($role === 'teacher') ? 't' : 's');
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(account_id, 2) AS UNSIGNED)) FROM users WHERE account_id LIKE CONCAT(?, '%')");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $stmt->bind_result($max_id);
    $stmt->fetch();
    $stmt->close();

    $next_id = $max_id + 1; // Increment the ID
    $formatted_id = $prefix . str_pad($next_id, 6, '0', STR_PAD_LEFT); // e.g., s000001

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO users (account_id, name, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $formatted_id, $name, $username, $email, $password, $role);

    if ($stmt->execute()) {
        echo "Signup Successful! Your account ID is: " . $formatted_id;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
