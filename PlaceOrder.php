<?php
require 'db.php';
require 'vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$cart_query = "SELECT cart_data FROM user_carts WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$subtotal = 0;
$delivery_fee = 38.00; 
$promo_discount = 0;
$free_cokes = 0;
$free_cokes_value = 0;

if (isset($_SESSION['pending_order']) && !empty($_SESSION['pending_order']['items'])) {
    $cart_items = $_SESSION['pending_order']['items'];
} elseif ($result->num_rows > 0) {
    $cart_data = $result->fetch_assoc();
    $cart_items = json_decode($cart_data['cart_data'], true);
}

// Function to check and add free cokes for Taposilog orders
function addFreeCokesPromo(&$cart_items) {
    $taposilog_count = 0;
    
    // Count total Taposilog orders - check for various silog items
    foreach ($cart_items as $item) {
        $foodName = strtolower($item['foodName']);
        if (strpos($foodName, 'silog') !== false || 
            strpos($foodName, 'taposilog') !== false ||
            strpos($foodName, 'tapsilog') !== false) {
            $taposilog_count += $item['quantity'];
        }
    }
    
    // Calculate free cokes (1 free coke per 5 taposilog orders)
    $free_cokes_quantity = floor($taposilog_count / 5);
    
    // Remove existing free coke items to avoid duplicates
    $cart_items = array_filter($cart_items, function($item) {
        return !(isset($item['is_free_promo']) && $item['is_free_promo'] === true);
    });
    
    // Re-index the array
    $cart_items = array_values($cart_items);
    
    // Add new free coke item if eligible
    if ($free_cokes_quantity > 0) {
        $cart_items[] = [
            'foodId' => 36, // Used for stock updates, but not inserted into order_items
            'foodName' => 'Coke 1.5 L (FREE - Taposilog Promo)',
            'price' => 0.00,
            'original_price' => 75.00,
            'quantity' => $free_cokes_quantity,
            'image_path' => 'coke.png',
            'category' => 'Beverages & Extra',
            'is_free_promo' => true,
            'promo_sets' => floor($taposilog_count / 5)
        ];
    }
    
    return [
        'free_cokes_count' => $free_cokes_quantity,
        'free_cokes_value' => $free_cokes_quantity * 75.00,
        'taposilog_count' => $taposilog_count
    ];
}

// Apply the free coke promotion
$promo_results = addFreeCokesPromo($cart_items);
$free_cokes = $promo_results['free_cokes_count'];
$free_cokes_value = $promo_results['free_cokes_value'];

// Calculate subtotal (free items have price 0)
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal - $promo_discount + $delivery_fee;

// Email sending function using PHPMailer
function sendOrderConfirmationEmail($order_details, $order_items, $customer_email) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jhimarcarlmotea23@gmail.com';
        $mail->Password = 'rtfz tcow tklk pbom';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
        // Sender and recipient
        $mail->setFrom('jhimarcarlmotea23@gmail.com', 'Hungry Potter');
        $mail->addAddress($customer_email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation - " . $order_details['order_number'] . " - Hungry Potter";
        $mail->Body = generateEmailContent($order_details, $order_items);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

function generateEmailContent($order_details, $order_items) {
    $order_data = json_decode($order_details['order_data'], true);
    $customer = $order_data['customer'];
    
    $promotions = isset($order_data['promotions_applied']) ? $order_data['promotions_applied'] : [];
    $free_cokes = isset($promotions['free_cokes']) ? $promotions['free_cokes'] : 0;
    $free_cokes_value = isset($promotions['free_cokes_value']) ? $promotions['free_cokes_value'] : 0;
    
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']);
    $manage_returns_url = $base_url . "/manage_returns.php?order_id=" . $order_details['order_id'] . "&token=" . md5($order_details['order_number'] . $customer['email']);
    
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Confirmation</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                max-width: 600px; 
                margin: 0 auto; 
                padding: 20px;
                background-color: #f9f9f9;
            }
            .email-container {
                background: white;
                border-radius: 10px;
                padding: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                text-align: center; 
                background: #bf0f0c; 
                color: white; 
                padding: 20px; 
                border-radius: 10px 10px 0 0;
                margin: -30px -30px 30px;
            }
            .header h1 { 
                margin: 0; 
                font-size: "Merrienda One", cursive;
            }
            .order-info { 
                background: #ffcfcf; 
                padding: 15px; 
                margin: 20px 0; 
            }
            .order-info h3 { 
                color: #bf0f0c; 
                margin-bottom: 8px;
            }
            .item { 
                border-bottom: 1px solid #eeeee; 
                padding: 10px 0; 
                display: flex;
                align-items: center;
                
            }
            .item:last-child { 
                border-bottom: none; 
            }
            .item-details {
                flex: 1;
            }
            .item-name { 
                font-weight: bold; 
                color: #bf0f0c; 
            }
            .item-price { 
                color: #666; 
                font-size: 14px; 
            }
            .item-total {
                font-weight: bold;
                color: #bf0f0c;
            }
            .free-item {
                background: #d4edda;
                border-radius: 5px;
                padding: 10px;
                margin-bottom: 5px;
0;
            }
            .free-item .item-name {
                color: #28a745;
            }
            .summary { 
                background: #f8f9fa; 
                padding: 15px; 
                border-radius: 5px;
                
                margin-bottom: 10px; 
                margin-top: 20px;
 
            }
            .summary-row .item { 
                display: flex; 
                justify-content: space-between; 
                margin: 5px 0; 
            }
            .promo-savings {
                color: #28a745;
                font-weight: bold;
            }
            .total { 
                font-weight: bold; 
                font-size: 18px; 
                color: #bf0f0c; 
                border-top: 2px solid #bf0f0c; 
                padding-top: 10px; 
                margin-top: 10px; 
            }
            .btn { 
                display: inline-block; 
                background: #bf0f0c; 
                color: white; 
                padding: 12px 25px; 
                text-decoration: none; 
                border-radius: 5px; 
                margin: 10px 5px;
                font-weight: bold;
                text-align: center;
            }
            .btn-outline {
                background: transparent;
                border: 2px solid #bf0f0c;
                color: #bf0f0c;
            }
            .btn:hover { 
                background: #a00d0a; 
            }
            .footer { 
                text-align: center; 
                margin-top: 30px; 
                padding-top: 20px; 
                border-top: 1px solid #eee; 
                color: #666; 
                font-size: 14px; 
            }
            .action-buttons {
                text-align: center;
                margin: 30px 0;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>🍽️ Hungry Potter</h1>
                <p style="margin: 10px 0 0 0;">Order Confirmation</p>
            </div>
            
            <h2 style="color: #28a745; text-align: center;">✅ Order Placed Successfully!</h2>
            <p style="text-align: center; font-size: 16px;">Thank you for your order, ' . htmlspecialchars($customer['name']) . '! Your delicious food is being prepared.</p>
            
            ' . ($free_cokes > 0 ? '
            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <h4 style="color: #28a745; margin-top: 0;">🎉 Congratulations! Free Cokes Added!</h4>
                <p style="margin-bottom: 0; color: #155724;">
                    You got <strong>' . $free_cokes . ' FREE Cokes</strong> with your Taposilog orders!<br>
                    <small>You saved ₱' . number_format($free_cokes_value, 2) . '</small>
                </p>
            </div>
            ' : '') . '
            
            <div class="order-info">
                <h3>📋 Order Details</h3>
                <p><strong>Order Number:</strong> ' . htmlspecialchars($order_details['order_number']) . '</p>
                <p><strong>Order Date:</strong> ' . date('F d, Y - g:i A', strtotime($order_details['order_date'])) . '</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">' . htmlspecialchars($order_details['status']) . '</span></p>
                <p><strong>Payment Method:</strong> ' . strtoupper(htmlspecialchars($order_details['payment_method'])) . '</p>
            </div>
            
            <div class="order-info">
                <h3>👤 Delivery Information</h3>
                <p><strong>Name:</strong> ' . htmlspecialchars($customer['name']) . '</p>
                <p><strong>Phone:</strong> ' . htmlspecialchars($customer['phone']) . '</p>
<p><strong>Address:</strong> ' . htmlspecialchars($order_data['customer']['address']) . '</p>            </div>
            
            <h3 style="color: #bf0f0c;">🍽️ Your Order Items</h3>';
    
    foreach ($order_items as $item) {
        $is_free = (isset($item['is_free_promo']) && $item['is_free_promo']) || strpos($item['food_name'], 'FREE') !== false;
        
        $html .= '
            <div class="item ' . ($is_free ? 'free-item' : '') . '">
                <div class="item-details">
                    <div class="item-name">' . htmlspecialchars($item['food_name']) . '</div>
                    <div class="item-price">';
        
        if ($is_free) {
            $html .= 'FREE (Taposilog Promo) - Was ₱75.00 each';
        } else {
            $html .= '₱' . number_format($item['food_price'], 2) . ' × ' . $item['quantity'];
        }
        
        $html .= '</div>
                </div>
                <div class="item-total">₱' . number_format($item['subtotal'], 2) . '</div>
            </div>';
    }
    
    $html .= '
            <div class="summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₱' . number_format($order_details['subtotal'], 2) . '</span>
                </div>';
    
    if ($free_cokes > 0) {
        $html .= '
                <div class="summary-row promo-savings">
                    <span>🎁 You Save (Taposilog Promo):</span>
                    <span>-₱' . number_format($free_cokes_value, 2) . '</span>
                </div>';
    }
    
    $html .= '
                <div class="summary-row">
                    <span>Promo Discount:</span>
                    <span>-₱' . number_format($order_details['promo_discount'], 2) . '</span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee:</span>
                    <span>₱' . number_format($order_details['delivery_fee'], 2) . '</span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span>₱' . number_format($order_details['order_total'], 2) . '</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <h3 style="color: #bf0f0c; margin-bottom: 15px;">Quick Actions</h3>
                <a href="' . $manage_returns_url . '" class="btn">
                    🔄 Manage Returns & Exchanges
                </a>
            </div>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <h4 style="color: #856404; margin-top: 0;">📞 Need Help?</h4>
                <p style="margin-bottom: 0; color: #856404;">
                    Contact us at <strong>support@hungrypotter.com</strong> or call <strong>(02) 123-4567</strong><br>
                    Our customer service is available 24/7 to assist you.
                </p>
            </div>
            
            <div class="footer">
                <p><strong>Hungry Potter Kim Tapsilogan</strong></p>
                <p>Serving you the best Filipino comfort food with a magical twist!</p>
                <p style="font-size: 12px; margin-top: 15px;">
                    This is an automated email. Please do not reply to this email address.<br>
                    If you did not place this order, please contact us immediately.
                </p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $payment_number = $_POST['payment_number'] ?? '';
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Validate stock availability
        foreach ($cart_items as $item) {
            if (!isset($item['is_free_promo']) || !$item['is_free_promo']) {
                $stmt = $conn->prepare("SELECT quantity, availability FROM fooditems WHERE foodId = ?");
                $stmt->bind_param("i", $item['foodId']);
                $stmt->execute();
                $result = $stmt->get_result();
                $food = $result->fetch_assoc();

                if (!$food || $food['availability'] !== 'Available' || $food['quantity'] < $item['quantity']) {
                    throw new Exception("Item {$item['foodName']} is not available or has insufficient stock.");
                }
            }
        }

        // Apply the promotion one more time before saving
        $final_promo_results = addFreeCokesPromo($cart_items);
        $final_free_cokes = $final_promo_results['free_cokes_count'];
        $final_free_cokes_value = $final_promo_results['free_cokes_value'];
        
        // Recalculate totals
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $total = $subtotal - $promo_discount + $delivery_fee;
        
        // Generate unique order number
        $order_number = 'HP' . date('Ymd') . sprintf('%04d', rand(1000, 9999));
        
        $order_data = json_encode([
            'items' => $cart_items,
            'customer' => [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address
            ],
            'payment_method' => $payment_method,
            'payment_number' => $payment_number,
            'subtotal' => $subtotal,
            'delivery_fee' => $delivery_fee,
            'promo_discount' => $promo_discount,
            'total' => $total,
            'promotions_applied' => [
                'free_cokes' => $final_free_cokes,
                'free_cokes_value' => $final_free_cokes_value,
                'taposilog_count' => $final_promo_results['taposilog_count']
            ]
        ]);
        
        // Insert into orders table
        $order_query = "INSERT INTO orders (user_id, order_number, payment_method, payment_number, subtotal, delivery_fee, promo_discount, order_total, order_data, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("isssdddds", $user_id, $order_number, $payment_method, $payment_number, $subtotal, $delivery_fee, $promo_discount, $total, $order_data);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Insert individual items into order_items table and update quantities
        $order_items = [];
        $updateStmt = $conn->prepare("UPDATE fooditems SET quantity = quantity - ?, availability = IF(quantity - ? <= 0, 'Not Available', 'Available') WHERE foodId = ?");
        
       foreach ($cart_items as $item) {
    $item_subtotal = $item['price'] * $item['quantity'];
    $is_free_promo = isset($item['is_free_promo']) && $item['is_free_promo'] ? 1 : 0;
    $created_at = date("Y-m-d H:i:s"); // Current datetime

    $item_query = "INSERT INTO order_items (order_id, food_name, food_price, quantity, subtotal, image_path, is_free_promo, created_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_query);
    $item_stmt->bind_param("isdddsss", $order_id, $item['foodName'], $item['price'], $item['quantity'], $item_subtotal, $item['image_path'], $is_free_promo, $created_at);
    $item_stmt->execute();

    // Update quantity in fooditems (skip free promo items)
    if (!$is_free_promo) {
        $updateStmt->bind_param("iii", $item['quantity'], $item['quantity'], $item['foodId']);
        $updateStmt->execute();
    }

    // Store for email
    $order_items[] = [
        'food_name' => $item['foodName'],
        'food_price' => $item['price'],
        'quantity' => $item['quantity'],
        'subtotal' => $item_subtotal,
        'image_path' => $item['image_path'],
        'is_free_promo' => $is_free_promo
    ];
}

        
        // Insert initial status into order_status_history
        $status_query = "INSERT INTO order_status_history (order_id, status, notes, updated_by) VALUES (?, 'Pending', 'Order placed successfully', ?)";
        $status_stmt = $conn->prepare($status_query);
        $status_stmt->bind_param("ii", $order_id, $user_id);
        $status_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Prepare order details for email
        $order_details = [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 'Pending',
            'payment_method' => $payment_method,
            'payment_number' => $payment_number,
            'subtotal' => $subtotal,
            'delivery_fee' => $delivery_fee,
            'promo_discount' => $promo_discount,
            'order_total' => $total,
            'order_data' => $order_data
        ];
        
        // Send confirmation email
        $email_sent = false;
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $email_sent = sendOrderConfirmationEmail($order_details, $order_items, $email);
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
            }
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM user_carts WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $_SESSION['cart'] = [];
        unset($_SESSION['pending_order']);
        
        // Set success message and redirect data
        $_SESSION['order_success'] = [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total' => $total,
            'payment_method' => $payment_method,
            'email_sent' => $email_sent,
            'free_cokes' => $final_free_cokes,
            'free_cokes_value' => $final_free_cokes_value
        ];
        
        header("Location: PlaceOrder.php?success=1");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Failed to place order: " . $e->getMessage();
        error_log($error_message);
        
        // Display error to user
        $_SESSION['order_error'] = $error_message;
        header("Location: PlaceOrder.php?error=1");
        exit;
    }
}

// Check if this is a success or error redirect
$show_receipt = false;
$order_details = [];
$promo_info = [];

if (isset($_GET['success']) && isset($_SESSION['order_success'])) {
    $show_receipt = true;
    $order_success = $_SESSION['order_success'];
    
    $promo_info = [
        'free_cokes' => isset($order_success['free_cokes']) ? $order_success['free_cokes'] : 0,
        'free_cokes_value' => isset($order_success['free_cokes_value']) ? $order_success['free_cokes_value'] : 0
    ];
    
    // Fetch complete order details
// Fetch complete order details
$order_query = "SELECT o.*, CONCAT(s.firstName, ' ', s.lastName) as customer_name, s.phoneNumber, s.email 
                FROM orders o 
                JOIN sign_up s ON o.user_id = s.userId 
                WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_success['order_id'], $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order_details = $result->fetch_assoc();
    $order_data = json_decode($order_details['order_data'], true);
    
    // Use the address from order_data instead of sign_up table
    $order_details['address'] = $order_data['customer']['address'] ?? '';
    
    if (isset($order_data['promotions_applied'])) {
        $promo_info = array_merge($promo_info, $order_data['promotions_applied']);
    }
    
    // Get order items
    $items_query = "SELECT * FROM order_items WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $order_success['order_id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $order_items = $items_result->fetch_all(MYSQLI_ASSOC);
}
    
    unset($_SESSION['order_success']);
} elseif (isset($_GET['error']) && isset($_SESSION['order_error'])) {
    $error_message = $_SESSION['order_error'];
    unset($_SESSION['order_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_receipt ? 'Order Receipt' : 'Place Order'; ?> - Hungry Potter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merienda+One&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #bf0f0c;
            --light-red: #ffcfcf;
            --dark-red: #6e0606;
            --border-red: #e52727;
            --blue-link: #1500ff;
            --white: #ffffff;
            --black: #000000;
            --gray-bg: #f5f5f5;
            --success-green: #28a745;
            --light-green: #d4edda;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray-bg);
            color: var(--black);
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://c.animaapp.com/NMMLUKIt/img/image-4.png');
            background-size: cover;
            background-position: center;
            height: 200px;
            display: flex;
            align-items: center;
            position: relative;
            margin-bottom: 30px;
        }
        
        .logo {
            height: 120px;
            margin-left: 30px;
            position: absolute;
            bottom: -30px;
            z-index: 10;
        }
        
        /* Success Message */
        .success-message {
            background: var(--light-green);
            color: var(--success-green);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid var(--success-green);
        }
        
        .success-message i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        
        .success-message h2 {
            margin-bottom: 10px;
            font-family: 'Merienda One', cursive;
        }
        
        /* Error Message */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid #f5c6cb;
        }
        
        /* Email Notification */
        .email-notification {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #bee5eb;
            text-align: center;
        }
        
        .email-notification.success {
            background: var(--light-green);
            color: var(--success-green);
            border-color: var(--success-green);
        }
        
        .email-notification.warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }
        
        /* Receipt Styles */
        .receipt {
            background: var(--white);
            border: 2px solid var(--primary-red);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed var(--primary-red);
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .receipt-title {
            font-family: 'Merienda One', cursive;
            font-size: 36px;
            color: var(--primary-red);
            margin-bottom: 10px;
        }
        
        .receipt-subtitle {
            font-size: 18px;
            color: #666;
        }
        
        /* Order Info */
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: var(--light-red);
            border-radius: 10px;
        }
        
        .info-group h4 {
            color: var(--primary-red);
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-group p {
            margin-bottom: 5px;
            color: #333;
        }
        
        /* Order Items */
        .order-items {
            margin-bottom: 25px;
        }
        
        .order-items h3 {
            color: var(--primary-red);
            margin-bottom: 15px;
            font-size: 22px;
            border-bottom: 1px solid var(--border-red);
            padding-bottom: 8px;
        }
        
        .receipt-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #fafafa;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-red);
        }
        
        .receipt-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
        }
        
        .receipt-item-details {
            flex: 1;
        }
        
        .receipt-item-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .receipt-item-price {
            color: #666;
            font-size: 14px;
        }
        
        .receipt-item-quantity {
            font-weight: 600;
            font-size: 16px;
            color: var(--primary-red);
            min-width: 60px;
            text-align: right;
        }
        
        .receipt-item-total {
            font-weight: 700;
            font-size: 16px;
            color: var(--dark-red);
            min-width: 80px;
            text-align: right;
        }
        
        /* Order Summary */
        .order-summary {
            background: var(--light-red);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .summary-row.total {
            border-top: 2px solid var(--primary-red);
            padding-top: 15px;
            margin-top: 15px;
            font-weight: 700;
            font-size: 20px;
            color: var(--dark-red);
        }
        
        /* Action Buttons */
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 5px;
        }
        
        .btn:hover {
            background: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-red);
            color: var(--primary-red);
        }
        
        .btn-outline:hover {
            background: var(--primary-red);
            color: var(--white);
        }
        
        .btn-success {
            background: var(--success-green);
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 20px;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
            }
            
            .header, .btn, .no-print {
                display: none !important;
            }
            
            .receipt {
                border: 1px solid #000;
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }
            
            .receipt-item {
                flex-direction: column;
                text-align: center;
            }
            
            .receipt-item-img {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="Logo.png" alt="Hungry Potter Logo" class="logo">
    </div>
    
    <div class="container">
        <?php if ($show_receipt && !empty($order_details)): ?>
            <!-- Success Message -->
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <h2>Order Placed Successfully!</h2>
                <p>Thank you for your order. Your food is being prepared.</p>
            </div>
            
            <!-- Email Notification -->
            <?php if (isset($order_success['email_sent'])): ?>
                <?php if ($order_success['email_sent']): ?>
                    <div class="email-notification success">
                        <i class="fas fa-envelope"></i>
                        <strong>Email Sent!</strong> Order confirmation has been sent to your email with a clickable "Manage Returns" link.
                    </div>
                <?php else: ?>
                    <div class="email-notification warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Email Issue:</strong> There was a problem sending the confirmation email, but your order was placed successfully.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Error Message -->
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h2>Error Placing Order</h2>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Receipt -->
            <div class="receipt">
                <div class="receipt-header">
                    <h1 class="receipt-title">Order Receipt</h1>
                    <p class="receipt-subtitle">Hungry Potter Kim Tapsilogan</p>
                    <p style="font-size: 14px; color: #666; margin-top: 10px;">
                        Order Date: <?php echo date('F d, Y - g:i A', strtotime($order_details['order_date'])); ?>
                    </p>
                </div>
                
                <!-- Order Information -->
                <div class="order-info">
                    <div class="info-group">
                        <h4><i class="fas fa-receipt"></i> Order Details</h4>
                        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order_details['order_number']); ?></p>
                        <p><strong>Status:</strong> <span style="color: var(--success-green);"><?php echo htmlspecialchars($order_details['status']); ?></span></p>
                        <p><strong>Payment Method:</strong> <?php echo strtoupper(htmlspecialchars($order_details['payment_method'])); ?></p>
                        <?php if (!empty($order_details['payment_number'])): ?>
                            <p><strong>Payment Number:</strong> <?php echo htmlspecialchars($order_details['payment_number']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="info-group">
                        <h4><i class="fas fa-user"></i> Customer Information</h4>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['phoneNumber']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="order-items">
                    <h3><i class="fas fa-utensils"></i> Order Items</h3>
                    <?php foreach ($order_items as $item): ?>
                        <div class="receipt-item">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['food_name']); ?>" class="receipt-item-img">
                            <div class="receipt-item-details">
                                <div class="receipt-item-name"><?php echo htmlspecialchars($item['food_name']); ?></div>
                                <div class="receipt-item-price">
                                    <?php
                                    if ($item['is_free_promo']) {
                                        echo 'FREE (Taposilog Promo)';
                                    } else {
                                        echo '₱' . number_format($item['food_price'], 2) . ' each';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="receipt-item-quantity">x<?php echo $item['quantity']; ?></div>
                            <div class="receipt-item-total">₱<?php echo number_format($item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($order_details['subtotal'], 2); ?></span>
                    </div>
                    <?php if ($promo_info['free_cokes'] > 0): ?>
                        <div class="summary-row" style="color: var(--success-green);">
                            <span>You Save (Taposilog Promo):</span>
                            <span>-₱<?php echo number_format($promo_info['free_cokes_value'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Promo Discount:</span>
                        <span>-₱<?php echo number_format($order_details['promo_discount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span>₱<?php echo number_format($order_details['delivery_fee'], 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <span>₱<?php echo number_format($order_details['order_total'], 2); ?></span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="text-center mt-3 no-print">
                    <button onclick="window.print()" class="btn btn-success">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                    <a href="manage_returns.php?order_id=<?php echo $order_details['order_id']; ?>&token=<?php echo md5($order_details['order_number'] . $order_details['email']); ?>" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Manage Returns
                    </a>
                    <a href="Menu.php" class="btn btn-outline">
                        <i class="fas fa-utensils"></i> Order Again
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- No Order Found or Error -->
            <div style="text-align: center; padding: 50px 20px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ffc107; margin-bottom: 20px;"></i>
                <h2><?php echo isset($error_message) ? 'Order Failed' : 'No Order Found'; ?></h2>
                <p style="margin-bottom: 30px;">
                    <?php echo isset($error_message) ? htmlspecialchars($error_message) : 'We couldn\'t find your order details. Please try placing your order again.'; ?>
                </p>
                <a href="checkout.php" class="btn">Return to Checkout</a>
                <a href="Menu.php" class="btn btn-outline">Back to Menu</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-redirect after successful order placement
        <?php if ($show_receipt): ?>
        // Optional: Auto-redirect to order tracking after some time
        // setTimeout(function() {
        //     window.location.href = 'order_tracking.php?order_id=<?php echo $order_details['order_id'] ?? ''; ?>';
        // }, 10000); // 10 seconds
        <?php endif; ?>
    </script>
</body>
</html>