<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = json_decode(file_get_contents('php://input'), true);
    
    if ($cart === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
        exit;
    }
    
    $_SESSION['cart'] = $cart; // Save cart to session
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>