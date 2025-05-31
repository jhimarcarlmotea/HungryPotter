<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root"; // change if needed
$pass = "";     // change if needed
$dbname = "hungry_potter";

// Use only PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get user information
$user_id = $_SESSION['user_id'];
$firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 
             (isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User');

// Handle reorder request
if (isset($_POST['reorder']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    error_log("Reordering order_id: $order_id"); // Debug log

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Verify order exists
        $check_order_stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
        $check_order_stmt->execute([$order_id]);
        if ($check_order_stmt->fetchColumn() == 0) {
            throw new Exception("No items found for order ID $order_id");
        }

        // Fetch order items for reordering
        $reorder_stmt = $pdo->prepare("
            SELECT oi.food_name, oi.food_price, oi.quantity, fi.foodId, fi.image_path, fi.description
            FROM order_items oi
            LEFT JOIN fooditems fi ON LOWER(oi.food_name) = LOWER(fi.foodName)
            WHERE oi.order_id = ?
        ");
        $reorder_stmt->execute([$order_id]);
        $reorder_items = $reorder_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($reorder_items)) {
            throw new Exception("No items found for this order");
        }

        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $added_items = [];
        foreach ($reorder_items as $item) {
            if ($item['foodId']) {
                $existing_item_key = null;
                foreach ($_SESSION['cart'] as $key => $cart_item) {
                    if ($cart_item['foodId'] == $item['foodId']) {
                        $existing_item_key = $key;
                        break;
                    }
                }
                
                if ($existing_item_key !== null) {
                    $_SESSION['cart'][$existing_item_key]['quantity'] += intval($item['quantity']);
                } else {
                    $_SESSION['cart'][] = [
                        'foodId' => intval($item['foodId']),
                        'foodName' => $item['food_name'],
                        'price' => floatval($item['food_price']),
                        'quantity' => intval($item['quantity']),
                        'image_path' => $item['image_path'] ?: 'default_image.jpg',
                        'description' => $item['description'] ?: ''
                    ];
                }
                $added_items[] = $item['food_name'];
            } else {
                error_log("Skipping item {$item['food_name']} as it has no matching foodId");
                $_SESSION['reorder_warning'] = "Some items could not be reordered as they are no longer available.";
            }
        }

        // Check if cart exists in database and update or insert
        $cart_json = json_encode($_SESSION['cart'], JSON_UNESCAPED_UNICODE);
        if ($cart_json === false) {
            throw new Exception("JSON encoding failed: " . json_last_error_msg());
        }

        $check_cart_stmt = $pdo->prepare("SELECT cart_id FROM user_carts WHERE user_id = ?");
        $check_cart_stmt->execute([$user_id]);
        $existing_cart = $check_cart_stmt->fetch();

        if ($existing_cart) {
            $update_cart_stmt = $pdo->prepare("
                UPDATE user_carts 
                SET cart_data = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $update_cart_stmt->execute([$cart_json, $user_id]);
        } else {
            $insert_cart_stmt = $pdo->prepare("
                INSERT INTO user_carts (user_id, cart_data, created_at, updated_at) 
                VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $insert_cart_stmt->execute([$user_id, $cart_json]);
        }

        // Commit transaction
        $pdo->commit();

        // Store success message and redirect to menu.php
        $_SESSION['reorder_success'] = true;
        $_SESSION['items_added'] = count($added_items);
        header("Location: Menu.php?reorder_success=1");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        error_log("Reorder error: " . $e->getMessage());
        $_SESSION['reorder_error'] = $e->getMessage();
        header("Location: orderHistory.php");
        exit();
    }
}

// Fetch user's order history with detailed items
$stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_date,
        o.order_total,
        o.status,
        o.order_number,
        o.payment_method,
        o.payment_number,
        o.subtotal,
        o.delivery_fee,
        o.promo_discount,
        o.notes
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");

try {
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch detailed items for each order
    foreach ($orders as &$order) {
        $items_stmt = $pdo->prepare("
            SELECT food_name, food_price, quantity
            FROM order_items 
            WHERE order_id = ?
        ");
        $items_stmt->execute([$order['order_id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $orders = [];
}

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';

// Debug information (remove in production)
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h3>Debug Information:</h3>";
    echo "<p><strong>User ID:</strong> " . htmlspecialchars($user_id) . "</p>";
    echo "<p><strong>Session Cart Items:</strong> " . (isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0) . "</p>";
    
    $debug_stmt = $pdo->prepare("SELECT cart_data, updated_at FROM user_carts WHERE user_id = ?");
    $debug_stmt->execute([$user_id]);
    $debug_cart = $debug_stmt->fetch();
    
    if ($debug_cart) {
        $cart_items = json_decode($debug_cart['cart_data'], true);
        echo "<p><strong>Database Cart Items:</strong> " . (is_array($cart_items) ? count($cart_items) : 0) . "</p>";
        echo "<p><strong>Last Updated:</strong> " . htmlspecialchars($debug_cart['updated_at']) . "</p>";
        echo "<p><strong>Cart Data:</strong> <pre>" . htmlspecialchars(json_encode($cart_items, JSON_PRETTY_PRINT)) . "</pre></p>";
    } else {
        echo "<p><strong>Database Cart:</strong> No cart found in database</p>";
    }
    echo "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order History - Hungry Potter</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="style2.css?v=<?php echo filemtime('style2.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Lato", Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: <?php echo $isAdmin ? '20px' : '100px'; ?>;
        }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 20px;
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(139, 0, 0, 0.3);
        }
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .orders-container {
            display: grid;
            gap: 20px;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #8B0000;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .order-id {
            font-size: 1.2rem;
            font-weight: bold;
            color: #8B0000;
        }
        .order-number {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .order-date {
            color: #666;
            font-size: 0.95rem;
        }
        .order-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-preparing {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }
        .status-ready,
        .status-out {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #ced4da;
        }
        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .order-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .order-items {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .order-items h4 {
            color: #8B0000;
            margin-bottom: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        .item-name {
            font-weight: 600;
            color: #333;
        }
        .item-details {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #666;
            font-size: 0.9rem;
        }
        .item-quantity {
            background-color: #8B0000;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .item-price {
            font-weight: 600;
            color: #8B0000;
        }
        .order-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        .info-item i {
            color: #8B0000;
            width: 20px;
        }
        .info-item span {
            color: #555;
            font-size: 0.95rem;
        }
        .payment-info {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #bee5eb;
        }
        .payment-info h4 {
            color: #0c5460;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .payment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .payment-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            text-transform: uppercase;
        }
        .payment-item.total {
            border-top: 2px solid #0c5460;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
        .reorder-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .reorder-btn:hover {
            background-color: #660000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
        }
        .reorder-btn:active {
            transform: translateY(0);
        }
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .no-orders i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        .no-orders h3 {
            color: #8B0000;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        .no-orders p {
            color: #666;
            margin-bottom: 20px;
        }
        .order-now-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .order-now-btn:hover {
            background-color: #660000;
            transform: translateY(-2px);
        }
        .back-nav {
            margin-bottom: 20px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }
            .order-details {
                grid-template-columns: 1fr;
            }
        }
        .back-to-home {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
        }
        .back-to-home:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1>Order History</h1>
            <p>Your previous orders at Hungry Potter</p>
        </div>

        <?php if (isset($_GET['reorder_success']) && $_GET['reorder_success'] == 1): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span>Your order items have been added to your cart!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['reorder_error'])): ?>
            <div class="success-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i>
                <span>Error: <?php echo htmlspecialchars($_SESSION['reorder_error']); ?></span>
            </div>
            <?php unset($_SESSION['reorder_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['reorder_warning'])): ?>
            <div class="success-message" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Warning: <?php echo htmlspecialchars($_SESSION['reorder_warning']); ?></span>
            </div>
            <?php unset($_SESSION['reorder_warning']); ?>
        <?php endif; ?>

        <div class="orders-container">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div class="order-number">Order ID: <?php echo htmlspecialchars($order['order_id']); ?></div>
                            </div>
                            <div class="order-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('M j, Y, g:i a', strtotime($order['order_date'])); ?>
                            </div>
                        </div>
                        <div class="order-details">
                            <div class="order-items">
                                <h4><i class="fas fa-list-ul"></i> Items</h4>
                                <div class="items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="item-row">
                                            <span class="item-name"><?php echo htmlspecialchars($item['food_name']); ?></span>
                                            <div class="item-details">
                                                <span class="item-quantity"><?php echo htmlspecialchars($item['quantity']); ?></span>
                                                <span class="item-price">₱<?php echo number_format($item['food_price'], 2); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="order-info">
                                <div class="info-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>
                                        <?php
                                        $status = strtolower($order['status']);
                                        $status_class = '';
                                        if ($status == 'pending') $status_class = 'status-pending';
                                        elseif ($status == 'confirmed') $status_class = 'status-confirmed';
                                        elseif ($status == 'preparing') $status_class = 'status-preparing';
                                        elseif ($status == 'ready' || $status == 'out') $status_class = 'status-ready';
                                        elseif ($status == 'delivered') $status_class = 'status-delivered';
                                        elseif ($status == 'cancelled') $status_class = 'status-cancelled';
                                        ?>
                                        <span class="order-status <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Subtotal: $<?php echo number_format($order['subtotal'], 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Delivery Fee: $<?php echo number_format($order['delivery_fee'], 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-tag"></i>
                                    <span>Promo Discount: $<?php echo number_format($order['promo_discount'], 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-receipt"></i>
                                    <span>Total: $<?php echo number_format($order['order_total'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($order['payment_method'])): ?>
                            <div class="payment-info">
                                <h4><i class="fas fa-credit-card"></i> Payment Method</h4>
                                <div class="payment-details">
                                    <div class="payment-item">
                                        <span>Type:</span>
                                        <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                    </div>
                                    <?php if (!empty($order['payment_number'])): ?>
                                        <div class="payment-item">
                                            <span>Last 4 Digits:</span>
                                            <span><?php echo htmlspecialchars($order['payment_number']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order['notes'])): ?>
                            <div class="info-item">
                                <i class="fas fa-sticky-note"></i>
                                <span>Notes: <?php echo htmlspecialchars($order['notes']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="order-actions">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                <button type="submit" name="reorder" class="reorder-btn">
                                    <i class="fas fa-redo"></i> Order Again
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-utensils"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet.</p>
                    <a href="Menu.php" class="order-now-btn">
                        <i class="fas fa-shopping-cart"></i> Order Now
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>
</body>
</html>