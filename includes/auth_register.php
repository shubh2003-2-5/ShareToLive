<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate that all required POST data is set
    if (isset($_POST['role'], $_POST['name'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        $role = $_POST['role'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($password !== $confirm_password) {
            die("❌ Passwords do not match!");
        }

        // Check if email is already registered
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            die("❌ Email already registered!");
        }

        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (role, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $role, $name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "✅ Registered successfully. <a href='../login.php'>Login</a>";
        } else {
            echo "❌ Something went wrong during registration.";
        }

        $stmt->close();
        $conn->close();

    } else {
        die("❌ Please fill in all required fields.");
    }
} else {
    die("❌ Invalid request.");
}
?>
