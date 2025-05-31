<?php
require 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart data from request
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
    exit;
}

$cart_data = json_encode($data['cart']);

// Check if user already has a cart
$check_query = "SELECT cart_id FROM user_carts WHERE user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing cart
    $update_query = "UPDATE user_carts SET cart_data = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $cart_data, $user_id);
} else {
    // Insert new cart
    $insert_query = "INSERT INTO user_carts (user_id, cart_data) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("is", $user_id, $cart_data);
}

if ($stmt->execute()) {
    // Also update session cart
    $_SESSION['cart'] = $data['cart'];
    echo json_encode(['success' => true, 'message' => 'Cart saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save cart: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>