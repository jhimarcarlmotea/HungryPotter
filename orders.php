<?php
require 'db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is admin (you might want to add role checking)
$user_id = $_SESSION['user_id'];
$role_query = "SELECT role FROM sign_up WHERE userId = ?";
$stmt = $conn->prepare($role_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role_result = $stmt->get_result();

if ($role_result->num_rows > 0) {
    $user_role = $role_result->fetch_assoc()['role'];
    if (strtolower($user_role) !== 'admin') {
        header("Location: Menu.php"); // Redirect non-admin users
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

// Handle status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $notes = $_POST['notes'] ?? '';
    
    // Update order status
    $update_query = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        // Add to status history
        $history_query = "INSERT INTO order_status_history (order_id, status, notes, updated_by) VALUES (?, ?, ?, ?)";
        $history_stmt = $conn->prepare($history_query);
        $history_stmt->bind_param("issi", $order_id, $new_status, $notes, $user_id);
        $history_stmt->execute();
        
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Failed to update order status.";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build the main query
$where_conditions = [];
$params = [];
$param_types = "";

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(o.order_date) = ?";
    $params[] = $date_filter;
    $param_types .= "s";
}

if (!empty($search_query)) {
    $where_conditions[] = "(o.order_number LIKE ? OR CONCAT(s.firstName, ' ', s.lastName) LIKE ? OR s.phoneNumber LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Get orders with customer information
$orders_query = "SELECT o.*, 
                        CONCAT(s.firstName, ' ', s.lastName) as customer_name,
                        s.phoneNumber, s.email, s.address
                 FROM orders o 
                 JOIN sign_up s ON o.user_id = s.userId 
                 $where_clause
                 ORDER BY o.order_date DESC";

$stmt = $conn->prepare($orders_query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$orders_result = $stmt->get_result();

// Get order statistics
$stats_query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                    SUM(CASE WHEN status = 'Preparing' THEN 1 ELSE 0 END) as preparing_orders,
                    SUM(CASE WHEN status = 'Out for Delivery' THEN 1 ELSE 0 END) as delivery_orders,
                    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(order_total) as total_revenue,
                    SUM(CASE WHEN DATE(order_date) = CURDATE() THEN order_total ELSE 0 END) as today_revenue
                FROM orders";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Hungry Potter Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merienda+One&display=swap" rel="stylesheet">
    <style>
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
    }
    
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
            --warning-orange: #fd7e14;
            --info-blue: #17a2b8;
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://c.animaapp.com/NMMLUKIt/img/image-4.png');
            background-size: cover;
            background-position: center;
            height: 150px;
            display: flex;
            align-items: center;
            position: relative;
            margin-bottom: 30px;
        }
        
        .logo {
            height: 80px;
            margin-left: 30px;
        }
        
        .page-title {
            font-family: 'Merienda One', cursive;
            font-size: 36px;
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-red);
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-red);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card.pending { border-left-color: var(--warning-orange); }
        .stat-card.confirmed { border-left-color: var(--info-blue); }
        .stat-card.preparing { border-left-color: #6f42c1; }
        .stat-card.delivery { border-left-color: #fd7e14; }
        .stat-card.delivered { border-left-color: var(--success-green); }
        .stat-card.cancelled { border-left-color: #dc3545; }
        .stat-card.revenue { border-left-color: #20c997; }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-red);
            display: block;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Filters */
        .filters {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: var(--dark-red);
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-success { background: var(--success-green); }
        .btn-success:hover { background: #218838; }
        
        .btn-warning { background: var(--warning-orange); }
        .btn-warning:hover { background: #e96500; }
        
        .btn-info { background: var(--info-blue); }
        .btn-info:hover { background: #138496; }
        
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        
        /* Orders Table */
        .orders-container {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .orders-table th {
            background: var(--primary-red);
            color: var(--white);
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .orders-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .orders-table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-preparing { background: #e2e3f1; color: #383d41; }
        .status-delivery { background: #ffeaa7; color: #856404; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        /* Order Items Preview */
        .order-items-preview {
            max-width: 200px;
            font-size: 12px;
        }
        
        .item-preview {
            background: #f8f9fa;
            padding: 3px 6px;
            margin: 2px 0;
            border-radius: 3px;
            display: inline-block;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            background: var(--primary-red);
            color: var(--white);
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .close {
            color: var(--white);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            opacity: 0.7;
        }
        
        /* Success/Error Messages */
        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .orders-table {
                font-size: 12px;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 8px 5px;
            }
        }
           .admin-panel {
            background-color: #ffffff;
            padding: 20px 0;
            border-bottom: 3px solid #8B0000;
            margin-bottom: 20px;
                font-family: "Lato", Helvetica;
                align-items:center;
        }
        
        .category-tabs {
            max-width: 1200px;
            margin: 0 auto;
            margin-right:200px
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
                    font-family: "Lato", Helvetica;
                    margin-left:200px;
        }
        
        .category-tab {
            padding: 12px 24px;
            border: 2px solid #8B0000;
            border-radius: 30px;
            background-color: #fff;
            color: #8B0000;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            
        }
        
        .category-tab.active,
        .category-tab:hover {
            background-color: #8B0000;
            color: #fff;
        }
        
        .category-tab i {
            font-size: 16px;
        }
        
        /* Admin Panel Title */
        .admin-title {
            padding: 12px 24px;
            border: 2px solid #8B0000;
            border-radius: 30px;
            background-color: #8B0000;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: default;
        font-family: "Lato", Helvetica;
        }
        
        /* Menu Section */
        .menu-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: <?php echo $isAdmin ? '60px' : '0'; ?>;
        }
        
        .category-title {
            text-align: center;
            margin: 40px 0 30px;
        }
        
        .category-title h2 {
            font-size: 36px;
            color: #8B0000;
            text-transform: uppercase;
        }
        
        .category-title p {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }
        
        .logout-btn, .login-btn {
            padding: 8px 16px;
            background-color: #8B0000;
            color: #fff;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .logout-btn:hover, .login-btn:hover {
            background-color: #660000;
            transform: translateY(-2px);
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
  <div class="admin-panel">
        <div class="category-tabs">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </div>
            <a href="Manageusers.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'Manageusers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Manage Users
            </a>
<a href="gallery_admin.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
    <i class="fas fa-images"></i> Gallery
</a>
            </a>
            <a href="orders.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Orders
            </a>
            <a href="MenuManagement.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'MenuManagement.php' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> Menu Management
            </a>
                    <a href="Returns.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'manage_returns.php' ? 'active' : ''; ?>">
            <i class="fas fa-undo"></i> Manage Returns
        </a>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">Orders Management</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['total_orders']; ?></span>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card pending">
                <span class="stat-number"><?php echo $stats['pending_orders']; ?></span>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card confirmed">
                <span class="stat-number"><?php echo $stats['confirmed_orders']; ?></span>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card preparing">
                <span class="stat-number"><?php echo $stats['preparing_orders']; ?></span>
                <div class="stat-label">Preparing</div>
            </div>
            <div class="stat-card delivery">
                <span class="stat-number"><?php echo $stats['delivery_orders']; ?></span>
                <div class="stat-label">Out for Delivery</div>
            </div>
            <div class="stat-card delivered">
                <span class="stat-number"><?php echo $stats['delivered_orders']; ?></span>
                <div class="stat-label">Delivered</div>
            </div>
            <div class="stat-card revenue">
                <span class="stat-number">₱<?php echo number_format($stats['total_revenue'], 2); ?></span>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card revenue">
                <span class="stat-number">₱<?php echo number_format($stats['today_revenue'], 2); ?></span>
                <div class="stat-label">Today's Revenue</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Confirmed" <?php echo $status_filter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="Preparing" <?php echo $status_filter === 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                            <option value="Out for Delivery" <?php echo $status_filter === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                            <option value="Delivered" <?php echo $status_filter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Order number, customer name, phone..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Orders Table -->
        <div class="orders-container">
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders_result->num_rows > 0): ?>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <?php
                                $order_data = json_decode($order['order_data'], true);
                                $items = $order_data['items'] ?? [];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <div><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></div>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php echo htmlspecialchars(substr($order['address'], 0, 50)) . (strlen($order['address']) > 50 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['phoneNumber']); ?></div>
                                        <div style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($order['email']); ?></div>
                                    </td>
                                    <td>
                                        <div class="order-items-preview">
                                            <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                                <div class="item-preview">
                                                    <?php echo htmlspecialchars($item['foodName']); ?> x<?php echo $item['quantity']; ?>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($items) > 2): ?>
                                                <div style="font-size: 11px; color: #666;">+<?php echo count($items) - 2; ?> more</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo strtoupper(htmlspecialchars($order['payment_method'])); ?></div>
                                        <?php if (!empty($order['payment_number'])): ?>
                                            <div style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($order['payment_number']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>₱<?php echo number_format($order['order_total'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $order['status'])); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                                        <div style="font-size: 12px; color: #666;"><?php echo date('g:i A', strtotime($order['order_date'])); ?></div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm" onclick="viewOrder(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="updateStatus(<?php echo $order['order_id']; ?>, '<?php echo htmlspecialchars($order['status']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 50px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                    <div>No orders found</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
                <br><br><br>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Order Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="orderDetails">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Order Status</h3>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="status_order_id" name="order_id">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div style="margin-bottom: 15px;">
                        <label for="new_status">New Status:</label>
                        <select name="new_status" id="new_status" class="form-control" required>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Preparing">Preparing</option>
                            <option value="Out for Delivery">Out for Delivery</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="notes">Notes (optional):</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes about this status update..."></textarea>
                    </div>
                    
                    <div style="text-align: right;">
                        <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>

    <script>
        function viewOrder(orderId) {
            // You can implement AJAX to load order details
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetails').innerHTML = html;
                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load order details');
                });
        }

        function updateStatus(orderId, currentStatus) {
            document.getElementById('status_order_id').value = orderId;
            document.getElementById('new_status').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
            const statusModal = document.getElementById('statusModal');
            
            if (event.target == orderModal) {
                orderModal.style.display = 'none';
            }
            if (event.target == statusModal) {
                statusModal.style.display = 'none';
            }
        }

        // Auto refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>