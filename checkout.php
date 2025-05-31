
<?php
require 'db.php';
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

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_items = $_SESSION['cart'];
} 
else if (isset($_SESSION['pending_order']) && !empty($_SESSION['pending_order']['items'])) {
    $cart_items = $_SESSION['pending_order']['items'];
} 
else if ($result->num_rows > 0) {
    $cart_data = $result->fetch_assoc();
    $cart_items = json_decode($cart_data['cart_data'], true);
}

// Function to check and add free cokes for Taposilog orders
function addFreeCokesPromo(&$cart_items) {
    $taposilog_count = 0;
    
    // Count total Taposilog orders - check for various silog items
    foreach ($cart_items as $item) {
        $foodName = strtolower($item['foodName']);
        // Check for any silog items
        if (strpos($foodName, 'silog') !== false || 
            strpos($foodName, 'taposilog') !== false ||
            strpos($foodName, 'tapsilog') !== false) {
            $taposilog_count += $item['quantity'];
        }
    }
    
    // Calculate free cokes (1 free coke per 5 taposilog orders)
    $free_cokes_quantity = floor($taposilog_count / 5);
    
    // DEBUG: Add some debugging info (remove in production)
    error_log("DEBUG: Taposilog count: " . $taposilog_count);
    error_log("DEBUG: Free cokes quantity: " . $free_cokes_quantity);
    foreach ($cart_items as $item) {
        error_log("DEBUG: Item name: " . $item['foodName'] . ", Qty: " . $item['quantity']);
    }
    
    // First, remove any existing free coke items to avoid duplicates
    $cart_items = array_filter($cart_items, function($item) {
        return !(isset($item['is_free_promo']) && $item['is_free_promo'] === true);
    });
    
    // Re-index the array after filtering
    $cart_items = array_values($cart_items);
    
    // Add new free coke item if eligible
    if ($free_cokes_quantity > 0) {
        $cart_items[] = [
            'id' => 36,
            'foodName' => 'Coke 1.5L (FREE - Taposilog Promo)',
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
        'free_cokes_value' => $free_cokes_quantity * 75.00, // Calculate savings value
        'taposilog_count' => $taposilog_count
    ];
}

// Apply the free coke promotion and get the results
$promo_results = addFreeCokesPromo($cart_items);
$free_cokes = $promo_results['free_cokes_count'];
$free_cokes_value = $promo_results['free_cokes_value'];

// Calculate subtotal (free items have price 0, so they don't add to subtotal)
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal - $promo_discount + $delivery_fee;

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';  // This will now capture the edited address
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $payment_number = $_POST['payment_number'] ?? '';
    
    // Validation for payment methods
    if ($payment_method === 'gcash' || $payment_method === 'paymaya') {
        if (empty($payment_number)) {
            $error_message = "Please enter your " . ucfirst($payment_method) . " number to proceed with the order.";
        } else {
            // Validate phone number format (basic validation)
            $payment_number = trim($payment_number);
            if (!preg_match('/^(09|\+639)\d{9}$/', $payment_number)) {
                $error_message = "Please enter a valid " . ucfirst($payment_method) . " number (e.g., 09XXXXXXXXX).";
            }
        }
    }
    
    // If no validation errors, proceed with order
    if (empty($error_message)) {
        // Apply the promotion one more time before saving to ensure accuracy
        $final_promo_results = addFreeCokesPromo($cart_items);
        $final_free_cokes = $final_promo_results['free_cokes_count'];
        $final_free_cokes_value = $final_promo_results['free_cokes_value'];
        
        // Recalculate totals after final promo application
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $total = $subtotal - $promo_discount + $delivery_fee;
        
        $order_data = json_encode([
            'items' => $cart_items,
            'customer' => [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address  // This will save the edited address
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
        
        $order_query = "INSERT INTO orders (user_id, order_data, order_total, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("isd", $user_id, $order_data, $total);
        
        if ($stmt->execute()) {
            $clear_cart = "DELETE FROM user_carts WHERE user_id = ?";
            $stmt = $conn->prepare($clear_cart);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $_SESSION['cart'] = [];
            unset($_SESSION['pending_order']);
            
            header("Location: order_confirmation.php?order_id=" . $conn->insert_id);
            exit;
        } else {
            $error_message = "Failed to place order. Please try again.";
        }
    }
}

// Fetch user information
$user_query = "SELECT CONCAT(firstName, ' ', lastName) AS name, phoneNumber, email, address FROM sign_up WHERE userId = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $name = $user_data['name'];
    $phone = $user_data['phoneNumber'];
    $email = $user_data['email'];
    $address = $user_data['address'];
} else {
    $name = $phone = $email = $address = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hungry Potter Checkout</title>
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
            --green: #28a745;
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
            max-width: 1200px;
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
        
        /* Page Title */
        .page-title {
            text-align: center;
            font-family: 'Merienda One', cursive;
            font-size: 42px;
            margin: 30px 0;
            color: var(--black);
        }
        
        /* Main Layout */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        /* Card Styles */
        .checkout-card {
            background: var(--white);
            border: 1px solid var(--primary-red);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: var(--white);
            border-bottom: 1px solid var(--border-red);
            padding: 15px 20px;
            font-weight: 700;
            font-size: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header .edit {
            color: var(--blue-link);
            font-size: 16px;
            font-weight: 400;
            cursor: pointer;
        }
        
        .card-body {
            padding: 20px;
            background: var(--light-red);
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 16px;
        }
        
        .form-control.error {
            border-color: #e74c3c;
            background-color: #ffeaea;
        }
        
        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }
        
        /* Payment Options */
        .payment-option {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: var(--white);
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-option:hover {
            border-color: var(--primary-red);
        }
        
        .payment-option.selected {
            border-color: var(--primary-red);
            background-color: rgba(191, 15, 12, 0.05);
        }
        
        .payment-option .radio {
            width: 18px;
            height: 18px;
            border: 1px solid #999;
            border-radius: 50%;
            margin-right: 15px;
            position: relative;
        }
        
        .payment-option.selected .radio::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--primary-red);
            border-radius: 50%;
            top: 3px;
            left: 3px;
        }
        
        .payment-option img {
            height: 24px;
            margin-left: 10px;
        }
        
        .payment-number-field {
            margin-top: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            display: none;
        }
        
        .payment-number-field.show {
            display: block;
        }
        
        .payment-error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .payment-error.show {
            display: block;
        }
        
        /* Order Items */
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--white);
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid rgba(110, 6, 6, 0.2);
            position: relative;
        }
        
        .order-item.free-item {
            border: 2px solid var(--green);
            background: rgba(40, 167, 69, 0.05);
        }
        
        .order-item.free-item::after {
            content: 'FREE!';
            position: absolute;
            top: -8px;
            right: 10px;
            background: var(--green);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .order-item:last-child {
            margin-bottom: 0;
        }
        
        .order-item-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .order-item-price {
            color: #666;
            font-size: 14px;
        }
        
        .order-item-quantity {
            font-weight: 600;
            font-size: 18px;
        }
        
        /* Order Summary */
        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-summary-item.free-item {
            color: var(--green);
            font-weight: 600;
        }
        
        .order-summary-item.savings {
            color: var(--green);
            font-weight: 600;
        }
        
        .order-total {
            border-top: 2px solid var(--black);
            padding-top: 15px;
            margin-top: 15px;
            font-weight: 700;
            font-size: 20px;
        }
        
        /* Action Buttons */
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn:hover {
            background: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-red);
            color: var(--primary-red);
        }
        
        .btn-outline:hover {
            background: rgba(191, 15, 12, 0.1);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        .mt-2 {
            margin-top: 10px;
        }
        
        /* Error Message */
        .error-message {
            background: #fdecea;
            color: #e74c3c;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #f5b7b1;
        }
        
        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-cart p {
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        /* Delivery Service Options */
        .delivery-service-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: var(--white);
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delivery-service-option:hover {
            border-color: var(--primary-red);
            background-color: rgba(191, 15, 12, 0.05);
        }

        .delivery-service-option img {
            height: 24px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="Logo.png" alt="Hungry Potter Logo" class="logo">
    </div>
    
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty. Please add items to your cart before checkout.</p>
                <a href="Menu.php" class="btn btn-outline">Return to Menu</a>
            </div>
        <?php else: ?>
        
        <form method="post" action="PlaceOrder.php" id="checkout-form">
            <div class="checkout-grid">
                <!-- Left Column -->
                <div class="left-column">
                    <!-- Delivery Address Card -->
                    <div class="checkout-card">
                        <div class="card-header">
                            Information
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea class="form-control" id="address" name="address" readonly><?php echo htmlspecialchars($address); ?></textarea>
                                <button type="button" id="edit-address-btn" class="btn btn-outline mt-2">Edit Address</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items Card -->
                    <div class="checkout-card">
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item <?php echo (isset($item['is_free_promo']) && $item['is_free_promo']) ? 'free-item' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['foodName']); ?>" class="order-item-img">
                                    <div class="order-item-details">
                                        <div class="order-item-name"><?php echo htmlspecialchars($item['foodName']); ?></div>
                                        <div class="order-item-price">
                                            <?php if (isset($item['is_free_promo']) && $item['is_free_promo']): ?>
                                                <span style="color: var(--green); font-weight: bold;">FREE (Taposilog Promo)</span>
                                                <br>
                                                <span style="text-decoration: line-through; font-size: 12px; color: #999;">
                                                    Was ₱<?php echo number_format($item['original_price'], 2); ?> each
                                                </span>
                                            <?php else: ?>
                                                ₱<?php echo number_format($item['price'], 2); ?> each
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="order-item-quantity">x<?php echo $item['quantity']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="right-column">
                    <!-- Payment Method Card -->
                    <div class="checkout-card">
                        <div class="card-header">
                            Payment Method
                        </div>
                        <div class="card-body">
                            <div class="payment-option selected" data-value="cod">
                                <div class="radio"></div>
                                <span>Cash on Delivery</span>
                            </div>
                            
                            <div class="payment-option" data-value="gcash">
                                <div class="radio"></div>
                                <span>GCash</span>
                                <img src="gcash.png" alt="GCash">
                            </div>
                            
                            <div id="gcash-number" class="payment-number-field">
                                <label for="gcash-payment-number">GCash Number *</label>
                                <input type="text" class="form-control" id="gcash-payment-number" name="payment_number" placeholder="09XXXXXXXXX" maxlength="11">
                                <div class="payment-error" id="gcash-error">Please enter a valid GCash number</div>
                            </div>
                            
                            <div class="payment-option" data-value="paymaya">
                                <div class="radio"></div>
                                <span>PayMaya</span>
                                <img src="PayMaya.png" alt="PayMaya">
                            </div>
                            
                            <div id="paymaya-number" class="payment-number-field">
                                <label for="paymaya-payment-number">PayMaya Number *</label>
                                <input type="text" class="form-control" id="paymaya-payment-number" name="payment_number" placeholder="09XXXXXXXXX" maxlength="11">
                                <div class="payment-error" id="paymaya-error">Please enter a valid PayMaya number</div>
                            </div>
                        
                            <!-- Foodpanda option without radio button -->
                            <div class="delivery-service-option" onclick="window.open('https://www.foodpanda.ph/restaurant/x4jm/hungry-potter-kim-tapsilogan-west-rembo', '_blank')">
                                <span>Foodpanda</span>
                                <img src="Foodpanda.jpeg" alt="Foodpanda">
                            </div>
                            
                            <input type="hidden" name="payment_method" id="payment_method" value="cod">
                        </div>
                    </div>
                    
                    <!-- Order Summary Card -->
                    <div class="checkout-card">
                        <div class="card-header">
                            Order Summary
                        </div>
                        <div class="card-body">
                            <div class="order-summary-item">
                                <span>Sub Total:</span>
                                <span>₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <?php if ($free_cokes > 0): ?>
                                <div class="order-summary-item free-item">
                                    <span><i class="fas fa-gift"></i> Coke 1.5 L (FREE):</span>
                                    <span>x<?php echo $free_cokes; ?> - ₱0.00</span>
                                </div>
                                <div class="order-summary-item savings">
                                    <span>You Save (Taposilog Promo):</span>
                                    <span>-₱<?php echo number_format($free_cokes_value, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($promo_discount > 0): ?>
                                <div class="order-summary-item">
                                    <span>Additional Promo Discount:</span>
                                    <span>-₱<?php echo number_format($promo_discount, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="order-summary-item">
                                <span>Delivery Fee:</span>
                                <span>₱<?php echo number_format($delivery_fee, 2); ?></span>
                            </div>
                            <div class="order-summary-item order-total">
                                <span>Total:</span>
                                <span>₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <button type="submit" class="btn btn-block mt-3">Place Order</button>
                            <div class="text-center mt-3">
                                <a href="Menu.php" class="btn btn-outline">Return to Menu</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Payment method selection
            const paymentOptions = document.querySelectorAll('.payment-option:not([onclick])');
            const paymentMethodInput = document.getElementById('payment_method');
            const gcashNumberField = document.getElementById('gcash-number');
            const paymayaNumberField = document.getElementById('paymaya-number');
            const gcashInput = document.getElementById('gcash-payment-number');
            const paymayaInput = document.getElementById('paymaya-payment-number');
            const gcashError = document.getElementById('gcash-error');
            const paymayaError = document.getElementById('paymaya-error');
            const checkoutForm = document.getElementById('checkout-form');

            // Function to validate phone number
            function validatePhoneNumber(phoneNumber) {
                const phoneRegex = /^(09|\+639)\d{9}$/;
                return phoneRegex.test(phoneNumber.trim());
            }

            // Function to show/hide payment number fields
            function togglePaymentFields(paymentMethod) {
                // Hide all payment fields first
                gcashNumberField.classList.remove('show');
                paymayaNumberField.classList.remove('show');
                gcashError.classList.remove('show');
                paymayaError.classList.remove('show');
                
                // Clear error styling
                gcashInput.classList.remove('error');
                paymayaInput.classList.remove('error');
                
                // Show appropriate field based on payment method
                if (paymentMethod === 'gcash') {
                    gcashNumberField.classList.add('show');
                } else if (paymentMethod === 'paymaya') {
                    paymayaNumberField.classList.add('show');
                }
            }

            // Payment option click handlers
            paymentOptions.forEach(option => {
                option.addEventListener('click', function () {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    const paymentMethod = this.getAttribute('data-value');
                    paymentMethodInput.value = paymentMethod;

                    togglePaymentFields(paymentMethod);
                });
            });

            // Real-time validation for payment numbers
            gcashInput.addEventListener('input', function() {
                const value = this.value.trim();
                if (value && !validatePhoneNumber(value)) {
                    this.classList.add('error');
                    gcashError.classList.add('show');
                } else {
                    this.classList.remove('error');
                    gcashError.classList.remove('show');
                }
            });

            paymayaInput.addEventListener('input', function() {
                const value = this.value.trim();
                if (value && !validatePhoneNumber(value)) {
                    this.classList.add('error');
                    paymayaError.classList.add('show');
                } else {
                    this.classList.remove('error');
                    paymayaError.classList.remove('show');
                }
            });

            // Form submission validation
            checkoutForm.addEventListener('submit', function (event) {
                const selectedMethod = paymentMethodInput.value;
                let hasError = false;

                // Validate GCash payment
                if (selectedMethod === 'gcash') {
                    const gcashValue = gcashInput.value.trim();
                    if (!gcashValue) {
                        event.preventDefault();
                        gcashInput.classList.add('error');
                        gcashError.textContent = 'Please enter your GCash number';
                        gcashError.classList.add('show');
                        hasError = true;
                    } else if (!validatePhoneNumber(gcashValue)) {
                        event.preventDefault();
                        gcashInput.classList.add('error');
                        gcashError.textContent = 'Please enter a valid GCash number (e.g., 09XXXXXXXXX)';
                        gcashError.classList.add('show');
                        hasError = true;
                    }
                }

                // Validate PayMaya payment
                if (selectedMethod === 'paymaya') {
                    const paymayaValue = paymayaInput.value.trim();
                    if (!paymayaValue) {
                        event.preventDefault();
                        paymayaInput.classList.add('error');
                        paymayaError.textContent = 'Please enter your PayMaya number';
                        paymayaError.classList.add('show');
                        hasError = true;
                    } else if (!validatePhoneNumber(paymayaValue)) {
                        event.preventDefault();
                        paymayaInput.classList.add('error');
                        paymayaError.textContent = 'Please enter a valid PayMaya number (e.g., 09XXXXXXXXX)';
                        paymayaError.classList.add('show');
                        hasError = true;
                    }
                }

                // Prevent form submission if Foodpanda or Grabfood is selected
                if (selectedMethod === 'foodpanda' || selectedMethod === 'grabfood') {
                    event.preventDefault();
                    alert('Please complete your order through the selected delivery service.');
                    return;
                }

                // Scroll to error if there is one
                if (hasError) {
                    const errorElement = document.querySelector('.payment-error.show');
                    if (errorElement) {
                        errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Edit address functionality - FIXED VERSION
            const editAddressBtn = document.getElementById('edit-address-btn');
            const addressField = document.getElementById('address');
            let isEditing = false;

            editAddressBtn.addEventListener('click', function() {
                isEditing = !isEditing;
                
                if (isEditing) {
                    // Enable editing
                    addressField.readOnly = false;
                    addressField.style.backgroundColor = '#fff';
                    addressField.style.border = '1px solid var(--primary-red)';
                    editAddressBtn.textContent = 'Save Address';
                    addressField.focus();
                } else {
                    // Save and disable editing
                    addressField.readOnly = true;
                    addressField.style.backgroundColor = '';
                    addressField.style.border = '1px solid #ddd';
                    editAddressBtn.textContent = 'Edit Address';
                    
                    // Show confirmation that address was updated
                    const originalText = editAddressBtn.textContent;
                    editAddressBtn.textContent = 'Address Saved!';
                    editAddressBtn.style.backgroundColor = 'var(--green)';
                    editAddressBtn.style.color = 'white';
                    
                    setTimeout(() => {
                        editAddressBtn.textContent = originalText;
                        editAddressBtn.style.backgroundColor = '';
                        editAddressBtn.style.color = '';
                    }, 2000);
                }
            });

            // Format phone number input (optional enhancement)
            function formatPhoneNumber(input) {
                input.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, ''); // Remove non-digits
                    
                    // Limit to 11 digits for Philippine mobile numbers
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }
                    
                    this.value = value;
                });
                
                input.addEventListener('keypress', function(e) {
                    // Only allow numbers
                    if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                        e.preventDefault();
                    }
                });
            }

            // Apply formatting to payment number inputs
            formatPhoneNumber(gcashInput);
            formatPhoneNumber(paymayaInput);
        });
    </script>
</body>
</html>