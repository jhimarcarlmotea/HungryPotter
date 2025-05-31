<?php
require 'db.php';
session_start();

// Remove admin-only restriction - allow all logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Create return_requests table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS return_requests (
    return_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    return_number VARCHAR(50) NOT NULL UNIQUE,
    return_type ENUM('refund', 'exchange') NOT NULL,
    return_reason TEXT NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    return_data JSON,
    status ENUM('Pending', 'Approved', 'Rejected', 'Processing', 'Completed') DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
)";
$conn->query($create_table_query);

// Get order_id and token from URL
$order_id = $_GET['order_id'] ?? 0;
$token = $_GET['token'] ?? '';

    // If no order_id or token, show user's orders to select from
if (!$order_id || !$token) {
    // Get user's orders with email
    $user_orders_query = "SELECT o.*, CONCAT(s.firstName, ' ', s.lastName) as customer_name, s.email
                          FROM orders o 
                          JOIN sign_up s ON o.user_id = s.userId 
                          WHERE o.user_id = ? 
                          ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($user_orders_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Show order selection page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Select Order for Return - Hungry Potter</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merienda+One&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-red: #bf0f0c;
                --light-red: #ffcfcf;
                --dark-red: #6e0606;
                --white: #ffffff;
                --gray-bg: #f5f5f5;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Poppins', sans-serif;
                background-color: var(--gray-bg);
                color: #000;
                line-height: 1.6;
            }
            
            .container {
                max-width: 900px;
                margin: 0 auto;
                padding: 20px;
            }
            
            .card {
                background: var(--white);
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                margin-bottom: 30px;
                border: 2px solid var(--primary-red);
            }
            
            .card-title {
                font-family: 'Merienda One', cursive;
                font-size: 32px;
                color: var(--primary-red);
                text-align: center;
                margin-bottom: 30px;
            }
            
            .order-item {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                transition: all 0.3s ease;
            }
            
            .order-item:hover {
                border-color: var(--primary-red);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .order-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .order-number {
                font-weight: 600;
                color: var(--primary-red);
                font-size: 18px;
            }
            
            .order-date {
                color: #666;
                font-size: 14px;
            }
            
            .order-details {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }
            
            .detail-item {
                font-size: 14px;
            }
            
            .detail-label {
                font-weight: 600;
                color: var(--dark-red);
            }
            
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: var(--primary-red);
                color: var(--white);
                border: none;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .btn:hover {
                background: var(--dark-red);
                transform: translateY(-2px);
            }
            
            .no-orders {
                text-align: center;
                padding: 50px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1 class="card-title">🔄 Select Order for Return</h1>
                
                <?php if (empty($user_orders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag" style="font-size: 48px; margin-bottom: 20px; color: #ccc;"></i>
                        <h3>No orders found</h3>
                        <p>You don't have any orders yet.</p>
                        <a href="Menu.php" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-utensils"></i> Order Now
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($user_orders as $order): ?>
                        <?php
                        // Create token using email from the fetched order data
                        $email = isset($order['email']) ? $order['email'] : '';
                        $order_token = md5($order['order_number'] . $email);
                        ?>
                        <div class="order-item">
                            <div class="order-header">
                                <div class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div class="order-date"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                            </div>
                            
                            <div class="order-details">
                                <div class="detail-item">
                                    <div class="detail-label">Status:</div>
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Total:</div>
                                    ₱<?php echo number_format($order['order_total'], 2); ?>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Payment:</div>
                                    <?php echo strtoupper(htmlspecialchars($order['payment_method'])); ?>
                                </div>
                            </div>
                            
                            <a href="?order_id=<?php echo $order['order_id']; ?>&token=<?php echo $order_token; ?>" class="btn">
                                <i class="fas fa-undo"></i> Request Return
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Fetch order details and validate token
$order_query = "SELECT o.*, CONCAT(s.firstName, ' ', s.lastName) as customer_name, s.phoneNumber, s.email, s.address 
                FROM orders o 
                JOIN sign_up s ON o.user_id = s.userId 
                WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_returns.php");
    exit;
}

$order_details = $result->fetch_assoc();
$expected_token = md5($order_details['order_number'] . $order_details['email']);

if ($token !== $expected_token) {
    header("Location: manage_returns.php");
    exit;
}

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Check if return request already exists
$return_query = "SELECT * FROM return_requests WHERE order_id = ?";
$return_stmt = $conn->prepare($return_query);
$return_stmt->bind_param("i", $order_id);
$return_stmt->execute();
$return_result = $return_stmt->get_result();
$existing_return = $return_result->fetch_assoc();

$success_message = '';
$error_message = '';

// Process return request
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$existing_return) {
    $return_reason = $_POST['return_reason'] ?? '';
    $return_type = $_POST['return_type'] ?? 'refund';
    $selected_items = $_POST['selected_items'] ?? [];
    $additional_notes = $_POST['additional_notes'] ?? '';
    
    if (empty($return_reason) || empty($selected_items)) {
        $error_message = "Please select items and provide a reason for the return.";
    } else {
        // Generate return request number
        $return_number = 'RET' . date('Ymd') . sprintf('%04d', rand(1000, 9999));
        
        // Calculate return amount
        $return_amount = 0;
        $return_items = [];
        
        foreach ($selected_items as $item_id) {
            foreach ($order_items as $item) {
                if ($item['id'] == $item_id) {
                    $return_amount += $item['subtotal'];
                    $return_items[] = $item;
                    break;
                }
            }
        }
        
        $return_data = json_encode([
            'items' => $return_items,
            'reason' => $return_reason,
            'type' => $return_type,
            'notes' => $additional_notes,
            'return_amount' => $return_amount
        ]);
        
        // Insert return request
        $insert_query = "INSERT INTO return_requests (order_id, return_number, return_type, return_reason, return_amount, return_data, status, request_date) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isssds", $order_id, $return_number, $return_type, $return_reason, $return_amount, $return_data);
        
        if ($insert_stmt->execute()) {
            $success_message = "Return request submitted successfully! Reference: " . $return_number;
            
            // Refresh to get the new return request
            $return_stmt->execute();
            $return_result = $return_stmt->get_result();
            $existing_return = $return_result->fetch_assoc();
        } else {
            $error_message = "Failed to submit return request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Returns - Hungry Potter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merienda+One&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #bf0f0c;
            --light-red: #ffcfcf;
            --dark-red: #6e0606;
            --border-red: #e52727;
            --white: #ffffff;
            --black: #000000;
            --gray-bg: #f5f5f5;
            --success-green: #28a745;
            --light-green: #d4edda;
            --warning-yellow: #ffc107;
            --light-yellow: #fff3cd;
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
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
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
        
        .card {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: 2px solid var(--primary-red);
        }
        
        .card-header {
            text-align: center;
            border-bottom: 2px dashed var(--primary-red);
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .card-title {
            font-family: 'Merienda One', cursive;
            font-size: 32px;
            color: var(--primary-red);
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-success {
            background: var(--light-green);
            color: var(--success-green);
            border: 1px solid var(--success-green);
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .order-info {
            background: var(--light-red);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .order-info h3 {
            color: var(--primary-red);
            margin-bottom: 15px;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-item strong {
            color: var(--dark-red);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-red);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-red);
        }
        
        .item-selection {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .item-checkbox {
            display: flex;
            align-items: center;
            background: var(--white);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .item-checkbox:hover {
            border-color: var(--primary-red);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .item-checkbox input[type="checkbox"] {
            margin-right: 15px;
            transform: scale(1.2);
        }
        
        .item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #666;
            font-size: 14px;
        }
        
        .item-total {
            font-weight: 700;
            color: var(--primary-red);
        }
        
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
        
        .return-status {
            text-align: center;
            padding: 30px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .status-pending {
            background: var(--light-yellow);
            color: #856404;
        }
        
        .status-approved {
            background: var(--light-green);
            color: var(--success-green);
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .text-center {
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: 1fr;
            }
            
            .item-checkbox {
                flex-direction: column;
                text-align: center;
            }
            
            .item-img {
                margin-bottom: 10px;
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
    <div class="header">
        <img src="Logo.png" alt="Hungry Potter Logo" class="logo">
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">🔄 Manage Returns & Exchanges</h1>
                <p>Order #<?php echo htmlspecialchars($order_details['order_number']); ?></p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Order Information -->
            <div class="order-info">
                <h3><i class="fas fa-receipt"></i> Order Information</h3>
                <div class="order-info-grid">
                    <div>
                        <div class="info-item">
                            <strong>Order Number:</strong> <?php echo htmlspecialchars($order_details['order_number']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Order Date:</strong> <?php echo date('F d, Y', strtotime($order_details['order_date'])); ?>
                        </div>
                        <div class="info-item">
                            <strong>Status:</strong> <?php echo htmlspecialchars($order_details['status']); ?>
                        </div>
                    </div>
                    <div>
                        <div class="info-item">
                            <strong>Customer:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Total Amount:</strong> ₱<?php echo number_format($order_details['order_total'], 2); ?>
                        </div>
                        <div class="info-item">
                            <strong>Payment Method:</strong> <?php echo strtoupper(htmlspecialchars($order_details['payment_method'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($existing_return): ?>
                <!-- Existing Return Request -->
                <div class="return-status">
                    <h3><i class="fas fa-clock"></i> Return Request Status</h3>
                    <div class="status-badge status-<?php echo strtolower($existing_return['status']); ?>">
                        <?php echo htmlspecialchars($existing_return['status']); ?>
                    </div>
                    <p><strong>Return Number:</strong> <?php echo htmlspecialchars($existing_return['return_number']); ?></p>
                    <p><strong>Request Date:</strong> <?php echo date('F d, Y - g:i A', strtotime($existing_return['request_date'])); ?></p>
                    <p><strong>Return Amount:</strong> ₱<?php echo number_format($existing_return['return_amount'], 2); ?></p>
                    <p><strong>Type:</strong> <?php echo ucfirst($existing_return['return_type']); ?></p>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p><strong>Reason:</strong> <?php echo htmlspecialchars($existing_return['return_reason']); ?></p>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Return Request Form -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> Select Items to Return/Exchange</label>
                        <div class="item-selection">
                            <?php foreach ($order_items as $item): ?>
                                <div class="item-checkbox">
                                    <input type="checkbox" name="selected_items[]" value="<?php echo $item['id']; ?>" id="item_<?php echo $item['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['food_name']); ?>" class="item-img">
                                    <div class="item-details">
                                        <div class="item-name"><?php echo htmlspecialchars($item['food_name']); ?></div>
                                        <div class="item-price">₱<?php echo number_format($item['food_price'], 2); ?> × <?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="item-total">₱<?php echo number_format($item['subtotal'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="return_type"><i class="fas fa-exchange-alt"></i> Return Type</label>
                        <select name="return_type" id="return_type" class="form-control" required>
                            <option value="refund">Refund</option>
                            <option value="exchange">Exchange</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="return_reason"><i class="fas fa-comment"></i> Reason for Return/Exchange</label>
                        <select name="return_reason" id="return_reason" class="form-control" required>
                            <option value="">Select a reason...</option>
                            <option value="Defective/Damaged Item">Defective/Damaged Item</option>
                            <option value="Wrong Item Delivered">Wrong Item Delivered</option>
                            <option value="Item Not as Described">Item Not as Described</option>
                            <option value="Quality Issues">Quality Issues</option>
                            <option value="Changed Mind">Changed Mind</option>
                            <option value="Late Delivery">Late Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_notes"><i class="fas fa-sticky-note"></i> Additional Notes (Optional)</label>
                        <textarea name="additional_notes" id="additional_notes" class="form-control" rows="4" placeholder="Please provide additional details about your return request..."></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane"></i> Submit Return Request
                        </button>
                        <a href="Menu.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Menu
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>
    <script>
        // Form validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const selectedItems = document.querySelectorAll('input[name="selected_items[]"]:checked');
            const returnReason = document.getElementById('return_reason').value;
            
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please select at least one item to return/exchange.');
                return;
            }
            
            if (!returnReason) {
                e.preventDefault();
                alert('Please select a reason for the return/exchange.');
                return;
            }
        });

        // Auto-check all items when first loading
        window.addEventListener('load', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateTotal);
            });
        });

        function updateTotal() {
            // You can add total calculation logic here if needed
        }
    </script>
</body>
</html>