<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if logged in as inneed
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'inneed') {
        die(json_encode(['status' => 'error', 'message' => 'Access Denied!']));
    }

    // Validate posted data
    $location = trim($_POST['location'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $inneed_id = $_SESSION['user']['id'];
    $cart_items = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];

    if (!$location) {
        die(json_encode(['status' => 'error', 'message' => 'Location is required.']));
    }

    // Check if cart has items
    if (empty($cart_items)) {
        die(json_encode(['status' => 'error', 'message' => 'Your cart is empty.']));
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Create a master request record
        $stmt = $conn->prepare("INSERT INTO requests (inneed_id, location, note, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $inneed_id, $location, $note);
        if (!$stmt->execute()) {
            throw new Exception("Failed to create request.");
        }
        
        $request_id = $conn->insert_id;
        $stmt->close();

        // Track successfully added items
        $successCount = 0;
        $added_items = [];
        $unavailable_items = [];

        foreach ($cart_items as $donation_id) {
            // Check if donation is still available
            $checkStmt = $conn->prepare("SELECT id, quantity, name FROM donations WHERE id = ? AND status = 'available'");
            $checkStmt->bind_param("i", $donation_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                $unavailable_items[] = $donation_id;
                $checkStmt->close();
                continue;
            }
            
            $donation = $result->fetch_assoc();
            $checkStmt->close();

            // Create request item
            $itemStmt = $conn->prepare("INSERT INTO request_items (request_id, donation_id, quantity) VALUES (?, ?, ?)");
            $itemStmt->bind_param("iii", $request_id, $donation_id, $donation['quantity']);
            
            if (!$itemStmt->execute()) {
                $itemStmt->close();
                continue;
            }
            $itemStmt->close();

            // Update donation status to 'reserved'
            $updateStmt = $conn->prepare("UPDATE donations SET status = 'reserved' WHERE id = ?");
            $updateStmt->bind_param("i", $donation_id);
            $updateStmt->execute();
            $updateStmt->close();

            $added_items[] = [
                'id' => $donation_id,
                'name' => $donation['name'],
                'quantity' => $donation['quantity']
            ];
            $successCount++;
        }

        if ($successCount === 0) {
            throw new Exception("None of the selected donations are available anymore.");
        }

        // Commit transaction
        $conn->commit();

        // Clear the cart only for successfully added items
        foreach ($added_items as $item) {
            if (isset($_SESSION['cart'][$item['id']])) {
                unset($_SESSION['cart'][$item['id']]);
            }
        }

        // Prepare email notification
        $to = "admin@example.com"; // Replace with real admin email
        $subject = "New Food Request #$request_id";
        $message = "A new food request has been placed.\n\n";
        $message .= "Request ID: $request_id\n";
        $message .= "User ID: $inneed_id\n";
        $message .= "Location: $location\n";
        $message .= "Note: $note\n\n";
        $message .= "Items requested:\n";
        
        foreach ($added_items as $item) {
            $message .= "- {$item['name']} (Qty: {$item['quantity']})\n";
        }
        
        if (!empty($unavailable_items)) {
            $message .= "\nThe following items were no longer available:\n";
            foreach ($unavailable_items as $item_id) {
                $message .= "- Item ID: $item_id\n";
            }
        }

        $headers = "From: notify@sharetolive.local";

        // Send email (suppress errors with @)
        @mail($to, $subject, $message, $headers);

        // Prepare response
        $response = [
            'status' => 'success', 
            'message' => 'Request placed successfully!',
            'request_id' => $request_id,
            'added_items' => $added_items,
            'unavailable_items' => $unavailable_items
        ];

        if (!empty($unavailable_items)) {
            $response['message'] .= ' Some items were no longer available.';
        }

        echo json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method.']);
}
?>