<?php
require 'db.php';
require 'vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

function sendReturnStatusEmail($email, $customer_name, $return_number, $order_number, $status, $admin_notes = '') {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jhimarcarlmotea23@gmail.com'; // Replace with your Gmail
        $mail->Password = 'rtfz tcow tklk pbom';    // Replace with your App Password
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
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Return Request Update - Hungry Potter (Return #$return_number)";
        
        // Customize email body based on status
        $status_messages = [
            'Approved' => 'Your return request has been approved. We will process it soon.',
            'Rejected' => 'We regret to inform you that your return request has been rejected.',
            'Processing' => 'Your return request is now being processed.',
            'Completed' => 'Your return request has been successfully completed.'
        ];
        
        $message = $status_messages[$status] ?? 'Your return request status has been updated.';
        
        // Conditionally include the menu link for Approved or Rejected statuses
        $menu_link = '';
        if ($status === 'Approved' || $status === 'Rejected') {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $menu_link = "<p><a href='$protocol://$host/menu.php'>Visit our menu</a> to explore more options.</p>";
        }
        
        $mail->Body = "
            <h2>Hello, $customer_name!</h2>
            <p>We have an update regarding your return request.</p>
            <p><strong>Return Number:</strong> $return_number</p>
            <p><strong>Order Number:</strong> $order_number</p>
            <p><strong>Status:</strong> $status</p>
            <p>$message</p>
            " . ($admin_notes ? "<p><strong>Admin Notes:</strong> $admin_notes</p>" : "") . "
            $menu_link
            <p>Thank you for choosing Hungry Potter!</p>
            <p>Best regards,<br>The Hungry Potter Team</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage()); // Log the error
        return false;
    }
}
// Handle status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $return_id = $_POST['return_id'] ?? 0;
    $action = $_POST['action'];
    $notes = $_POST['admin_notes'] ?? '';
    
    $new_status = '';
    switch ($action) {
        case 'approve':
            $new_status = 'Approved';
            break;
        case 'reject':
            $new_status = 'Rejected';
            break;
        case 'process':
            $new_status = 'Processing';
            break;
        case 'complete':
            $new_status = 'Completed';
            break;
    }
    
    if ($new_status && $return_id) {
        // Fetch return details for email
        $return_query = "SELECT r.return_number, r.return_amount, r.return_type, r.request_date, 
                               o.order_number, s.email, CONCAT(s.firstName, ' ', s.lastName) as customer_name
                        FROM return_requests r
                        JOIN orders o ON r.order_id = o.order_id
                        JOIN sign_up s ON o.user_id = s.userId
                        WHERE r.return_id = ?";
        $stmt = $conn->prepare($return_query);
        $stmt->bind_param("i", $return_id);
        $stmt->execute();
        $return_result = $stmt->get_result();
        $return_data = $return_result->fetch_assoc();

        $update_query = "UPDATE return_requests SET status = ?, updated_at = NOW() WHERE return_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $return_id);
        
        if ($stmt->execute()) {
            // Add admin notes if provided
            if ($notes) {
                $notes_query = "UPDATE return_requests SET return_data = JSON_SET(return_data, '$.admin_notes', ?) WHERE return_id = ?";
                $notes_stmt = $conn->prepare($notes_query);
                $notes_stmt->bind_param("si", $notes, $return_id);
                $notes_stmt->execute();
            }
            
            // Send email notification
            if ($return_data) {
                $email_sent = sendReturnStatusEmail(
                    $return_data['email'],
                    $return_data['customer_name'],
                    $return_data['return_number'],
                    $return_data['order_number'],
                    $new_status,
                    $notes
                );
                
                if ($email_sent) {
                    $success_message = "Return request updated successfully! Email notification sent to customer.";
                } else {
                    $success_message = "Return request updated successfully, but failed to send email notification.";
                }
            } else {
                $success_message = "Return request updated successfully, but customer data not found.";
            }
        } else {
            $error_message = "Failed to update return request.";
        }
    } else {
        $error_message = "Invalid status or return ID.";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];
$param_types = '';

if ($status_filter) {
    $where_conditions[] = "r.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if ($date_filter) {
    $where_conditions[] = "DATE(r.request_date) = ?";
    $params[] = $date_filter;
    $param_types .= 's';
}

if ($search) {
    $where_conditions[] = "(r.return_number LIKE ? OR o.order_number LIKE ? OR CONCAT(s.firstName, ' ', s.lastName) LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get return requests with order and customer details
$returns_query = "SELECT r.*, o.order_number, o.order_total, o.order_date, 
                         CONCAT(s.firstName, ' ', s.lastName) as customer_name, 
                         s.email, s.phoneNumber
                  FROM return_requests r
                  JOIN orders o ON r.order_id = o.order_id
                  JOIN sign_up s ON o.user_id = s.userId
                  $where_clause
                  ORDER BY r.request_date DESC";

$stmt = $conn->prepare($returns_query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$returns_result = $stmt->get_result();
$returns = $returns_result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats_query = "SELECT 
                    COUNT(*) as total_returns,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_returns,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_returns,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_returns,
                    SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) as processing_returns,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_returns,
                    SUM(return_amount) as total_amount
                FROM return_requests";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Returns Management - Hungry Potter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merienda+One&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #bf0f0c;
            --light-red: #ffcfcf;
            --dark-red: #6e0606;
            --white: #ffffff;
            --gray-bg: #f5f5f5;
            --success-green: #28a745;
            --light-green: #d4edda;
            --warning-yellow: #ffc107;
            --light-yellow: #fff3cd;
            --danger-red: #dc3545;
            --light-danger: #f8d7da;
            --info-blue: #17a2b8;
            --light-info: #d1ecf1;
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
         .admin-panel {
            background-color: #ffffff;
            padding: 20px 0;
            border-bottom: 3px solid #8B0000;
            margin-bottom: 20px;
            font-family: "Lato", Helvetica;
            align-items: center;
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
        
        .menu-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 60px;
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

 .admin-nav {
            background-color: #2c3e50;
            color: #fff;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-nav h3 {
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-nav h3 i {
            color: #e74c3c;
        }
        
        .admin-nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .admin-nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            color: #3498db;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid var(--primary-red);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-red);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .filters-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-red);
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-red);
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
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn:hover {
            background: var(--dark-red);
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-success {
            background: var(--success-green);
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: var(--danger-red);
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: var(--warning-yellow);
            color: #000;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-info {
            background: var(--info-blue);
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .returns-table {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: var(--primary-red);
            color: var(--white);
            font-weight: 600;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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
            background: var(--light-danger);
            color: var(--danger-red);
        }
        
        .status-processing {
            background: var(--light-info);
            color: var(--info-blue);
        }
        
        .status-completed {
            background: #d1f2eb;
            color: #00695c;
        }
        
        .return-details {
            font-size: 12px;
            color: #666;
        }
        
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
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-red);
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--primary-red);
        }
        
        .item-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: var(--white);
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .item:last-child {
            margin-bottom: 0;
        }
        
        .item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: var(--light-green);
            color: var(--success-green);
            border: 1px solid var(--success-green);
        }
        
        .alert-error {
            background: var(--light-danger);
            color: var(--danger-red);
            border: 1px solid var(--danger-red);
        }
        
        .no-returns {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .table-responsive {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
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
        <a href="gallery_admin.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'gallery_admin.php' ? 'active' : ''; ?>">
            <i class="fas fa-images"></i> Gallery
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
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_returns']; ?></div>
                <div class="stat-label">Total Returns</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending_returns']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['approved_returns']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['processing_returns']; ?></div>
                <div class="stat-label">Processing</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_returns']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">₱<?php echo number_format($stats['total_amount'], 0); ?></div>
                <div class="stat-label">Total Amount</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="status">Filter by Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="Processing" <?php echo $status_filter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Filter by Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Return #, Order #, Customer..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="?" class="btn btn-outline">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Returns Table -->
        <div class="returns-table">
            <div class="table-responsive">
                <?php if (empty($returns)): ?>
                    <div class="no-returns">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; color: #ccc;"></i>
                        <h3>No return requests found</h3>
                        <p>No return requests match your current filters.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Return #</th>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returns as $return): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($return['return_number']); ?></strong>
                                        <div class="return-details">
                                            <?php 
                                            $return_data = json_decode($return['return_data'], true);
                                            echo ucfirst($return_data['reason'] ?? 'N/A');
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($return['order_number']); ?></strong>
                                        <div class="return-details">
                                            ₱<?php echo number_format($return['order_total'], 2); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($return['customer_name']); ?></strong>
                                        <div class="return-details">
                                            <?php echo htmlspecialchars($return['email']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="text-transform: capitalize; font-weight: 600;">
                                            <?php echo htmlspecialchars($return['return_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>₱<?php echo number_format($return['return_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($return['status']); ?>">
                                            <?php echo htmlspecialchars($return['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($return['request_date'])); ?>
                                        <div class="return-details">
                                            <?php echo date('g:i A', strtotime($return['request_date'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewReturn(<?php echo $return['return_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($return['status'] === 'Pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $return['return_id']; ?>, 'approve')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="updateStatus(<?php echo $return['return_id']; ?>, 'reject')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($return['status'] === 'Approved'): ?>
                                            <button class="btn btn-sm btn-warning" onclick="updateStatus(<?php echo $return['return_id']; ?>, 'process')">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                        <?php elseif ($return['status'] === 'Processing'): ?>
                                            <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $return['return_id']; ?>, 'complete')">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Return Details Modal -->
    <div id="returnModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Return Details</h3>
                <span class="close">×</span>
            </div>
            <div id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Return Status</h3>
                <span class="close">×</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="return_id" id="statusReturnId">
                <input type="hidden" name="action" id="statusAction">
                
                <div class="form-group">
                    <label for="admin_notes">Admin Notes (Optional)</label>
                    <textarea name="admin_notes" id="admin_notes" class="form-control" rows="4" placeholder="Add any notes about this status update..."></textarea>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn" id="confirmButton">Confirm</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('statusModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
                    <br><br><br>
        <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>

    <script>
        // Return data for modal display
        const returnsData = <?php echo json_encode($returns); ?>;

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                // Set focus to modal for accessibility
                modal.querySelector('.modal-content').focus();
            } else {
                console.error(`Modal with ID ${modalId} not found`);
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                // Clear form fields in status modal
                if (modalId === 'statusModal') {
                    document.getElementById('admin_notes').value = '';
                }
            } else {
                console.error(`Modal with ID ${modalId} not found`);
            }
        }

        // View return details
        function viewReturn(returnId) {
            console.log(`Viewing return ID: ${returnId}`); // Debugging
            const returnData = returnsData.find(r => r.return_id == returnId);
            if (!returnData) {
                console.error(`Return data for ID ${returnId} not found`);
                return;
            }

            const data = JSON.parse(returnData.return_data);
            const modalBody = document.getElementById('modalBody');

            modalBody.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <h4>Return Information</h4>
                    <p><strong>Return Number:</strong> ${returnData.return_number}</p>
                    <p><strong>Order Number:</strong> ${returnData.order_number}</p>
                    <p><strong>Customer:</strong> ${returnData.customer_name}</p>
                    <p><strong>Email:</strong> ${returnData.email}</p>
                    <p><strong>Type:</strong> ${returnData.return_type}</p>
                    <p><strong>Amount:</strong> ₱${parseFloat(returnData.return_amount).toLocaleString()}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-${returnData.status.toLowerCase()}">${returnData.status}</span></p>
                    <p><strong>Request Date:</strong> ${new Date(returnData.request_date).toLocaleDateString()}</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4>Return Reason</h4>
                    <p>${data.reason || 'N/A'}</p>
                    ${data.notes ? `<p><strong>Additional Notes:</strong> ${data.notes}</p>` : ''}
                    ${data.admin_notes ? `<p><strong>Admin Notes:</strong> ${data.admin_notes}</p>` : ''}
                </div>
                
                <div>
                    <h4>Returned Items</h4>
                    <div class="item-list">
                        ${data.items.map(item => `
                            <div class="item">
                                <img src="${item.image_path}" alt="${item.food_name}" onerror="this.src='placeholder.jpg'">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">${item.food_name}</div>
                                    <div style="color: #666; font-size: 14px;">₱${parseFloat(item.food_price).toFixed(2)} × ${item.quantity}</div>
                                </div>
                                <div style="font-weight: 700; color: var(--primary-red);">₱${parseFloat(item.subtotal).toFixed(2)}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;

            openModal('returnModal');
        }

        // Update status
        function updateStatus(returnId, action) {
            console.log(`Updating status for return ID: ${returnId}, Action: ${action}`); // Debugging
            const actionText = {
                'approve': 'Approve',
                'reject': 'Reject',
                'process': 'Mark as Processing',
                'complete': 'Mark as Completed'
            };

            document.getElementById('statusReturnId').value = returnId;
            document.getElementById('statusAction').value = action;
            document.getElementById('confirmButton').textContent = `Confirm ${actionText[action]}`;

            openModal('statusModal');
        }

        // Event delegation for button clicks
        document.addEventListener('click', function(event) {
            const target = event.target.closest('button');
            if (!target) return;

            // Check if the button is for viewing or updating status
            if (target.classList.contains('btn-info')) {
                const returnId = target.getAttribute('onclick').match(/\d+/)[0];
                viewReturn(returnId);
                event.preventDefault();
            } else if (target.classList.contains('btn-success') || target.classList.contains('btn-danger') || target.classList.contains('btn-warning')) {
                const match = target.getAttribute('onclick').match(/updateStatus\((\d+),\s*'([^']+)'\)/);
                if (match) {
                    const returnId = match[1];
                    const action = match[2];
                    updateStatus(returnId, action);
                    event.preventDefault();
                }
            } else if (target.classList.contains('close')) {
                const modal = target.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // Initialize tooltips or additional functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Returns Management loaded');
            // Ensure modals are hidden on page load
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    </script>
</body>
</html>