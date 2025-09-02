<?php
session_start();
include '../includes/db.php';

// Make sure the request came from a form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all expected fields exist
    if (isset($_POST['email'], $_POST['password'], $_POST['role'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Query user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../pages/admin_dashboard.php");
                } elseif ($user['role'] === 'donator') {
                    header("Location: ../pages/donator_dashboard.php");
                } elseif ($user['role'] === 'inneed') {
                    header("Location: ../pages/inneed_dashboard.php");
                } else {
                    die("❌ Unknown role.");
                }

                exit();
            } else {
                die("❌ Invalid credentials!");
            }
        } else {
            die("❌ Invalid credentials!");
        }
    } else {
        die("❌ Missing form data.");
    }
} else {
    die("❌ Invalid request method.");
}
