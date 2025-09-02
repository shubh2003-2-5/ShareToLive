<?php
session_start();
include 'db.php';

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'donator') {
    die("Access Denied! You must be a donor to submit a donation.");
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $gstin = $_POST['gstin'] ?? '';
    $license = $_POST['license'] ?? '';
    $food_details = $_POST['food_details'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $location = $_POST['location'] ?? '';
    $donater_id = $_SESSION['user']['id'] ?? null;

    // Validate input fields
    if (empty($name) || empty($gstin) || empty($license) || empty($food_details) || empty($quantity) || empty($location)) {
        die("Please fill all the required fields.");
    }

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $_FILES['food_image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            die("Only JPG, JPEG, and PNG files are allowed.");
        }

        if ($_FILES['food_image']['size'] > 5 * 1024 * 1024) {
            die("File size exceeds the maximum limit of 5MB.");
        }

        $targetDir = "../uploads/";
        $timestamp = date("Ymd_His");
        $fileName = $timestamp . "_" . basename($_FILES["food_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["food_image"]["tmp_name"], $targetFilePath)) {
            $image_path = $fileName;
        } else {
            die("Image upload failed.");
        }
    }

    // Save donation record
    $stmt = $conn->prepare("INSERT INTO donations (donater_id, name, gstin, license, food_details, quantity, location, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $donater_id, $name, $gstin, $license, $food_details, $quantity, $location, $image_path);

    if ($stmt->execute()) {
        echo "✅ Donation saved successfully.";
    } else {
        echo "❌ Failed to save donation: " . $stmt->error;
    }
} else {
    echo "❌ Invalid Request.";
}
