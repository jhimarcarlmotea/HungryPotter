<?php
require 'db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "<div style='text-align: center; color: #721c24;'>Access denied. Please log in.</div>";
    exit;
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$role_query = "SELECT role FROM sign_up WHERE userId = ?";
$stmt = $conn->prepare($role_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role_result = $stmt->get_result();

if ($role_result->num_rows > 0) {
    $user_role = $role_result->fetch_assoc()['role'];
    if (strtolower($user_role) !== 'admin') {
        http_response_code(403);
        echo "<div style='text-align: center; color: #721c24;'>Access denied. Admin privileges required.</div>";
        exit;
    }
} else {
    http_response_code(403);
    echo "<div style='text-align: center; color: #721c24;'>Access denied. User not found.</div>";
    exit;
}

// Get order ID
$order_id = $_GET['order_id'] ?? null;

if (!$order_id || !is_numeric($order_id)) {
    echo "<div style='text-align: center; color: #721c24;'>Invalid order ID.</div>";
    exit;
}

// Get order details with customer information
$order_query = "SELECT o.*, 
                       CONCAT(s.firstName, ' ', s.lastName) as customer_name,
                       s.phoneNumber, s.email, s.address, s.userId
                FROM orders o 
                JOIN sign_up s ON o.user_id = s.userId 
                WHERE o.order_id = ?";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "<div style='text-align: center; color: #721c24;'>Order not found.</div>";
    exit;
}

$order = $order_result->fetch_assoc();
$order_data = json_decode($order['order_data'], true);
$items = $order_data['items'] ?? [];

// Get order status history
$history_query = "SELECT osh.*, CONCAT(s.firstName, ' ', s.lastName) as updated_by_name
                  FROM order_status_history osh
                  LEFT JOIN sign_up s ON osh.updated_by = s.userId
                  WHERE osh.order_id = ?
                  ORDER BY osh.updated_at DESC";

$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $order_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<style>
.order-detail-section {
    margin-bottom: 25px;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #bf0f0c;
    margin-bottom: 10px;
    border-bottom: 2px solid #bf0f0c;
    padding-bottom: 5px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.detail-item {
    background: white;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #bf0f0c;
}

.detail-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 5px;
    font-weight: 500;
}

.detail-value {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.items-table th {
    background: #bf0f0c;
    color: white;
    padding: 10px 8px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
}

.items-table td {
    padding: 8px;
    border-bottom: 1px solid #eee;
    font-size: 13px;
}

.items-table tr:nth-child(even) {
    background: #f8f9fa;
}

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

.history-item {
    background: white;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 5px;
    border-left: 3px solid #17a2b8;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.history-status {
    font-weight: 600;
    color: #bf0f0c;
}

.history-time {
    font-size: 12px;
    color: #666;
}

.history-by {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.history-notes {
    font-size: 13px;
    color: #333;
    font-style: italic;
}

.total-summary {
    background: white;
    padding: 15px;
    border-radius: 5px;
    text-align: right;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 14px;
}

.total-final {
    font-size: 18px;
    font-weight: bold;
    color: #bf0f0c;
    border-top: 2px solid #bf0f0c;
    padding-top: 10px;
}
</style>

<div class="order-detail-section">
    <div class="section-title">Order Information</div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Order Number</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['order_number']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $order['status'])); ?>">
                    <?php echo htmlspecialchars($order['status']); ?>
                </span>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Order Date</div>
            <div class="detail-value"><?php echo date('F d, Y g:i A', strtotime($order['order_date'])); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Payment Method</div>
            <div class="detail-value"><?php echo strtoupper(htmlspecialchars($order['payment_method'])); ?></div>
        </div>
        <?php if (!empty($order['payment_number'])): ?>
        <div class="detail-item">
            <div class="detail-label">Payment Reference</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['payment_number']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="order-detail-section">
    <div class="section-title">Customer Information</div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Customer Name</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Phone Number</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['phoneNumber']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['email']); ?></div>
        </div>
    </div>
    <div class="detail-item">
        <div class="detail-label">Delivery Address</div>
        <div class="detail-value"><?php echo htmlspecialchars($order['address']); ?></div>
    </div>
</div>

<div class="order-detail-section">
    <div class="section-title">Order Items</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            foreach ($items as $item): 
                $item_total = $item['price'] * $item['quantity'];
                $subtotal += $item_total;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['foodName']); ?></td>
                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($item_total, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total-summary">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₱<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <?php if (isset($order_data['delivery_fee']) && $order_data['delivery_fee'] > 0): ?>
        <div class="total-row">
            <span>Delivery Fee:</span>
            <span>₱<?php echo number_format($order_data['delivery_fee'], 2); ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row total-final">
            <span>Total:</span>
            <span>₱<?php echo number_format($order['order_total'], 2); ?></span>
        </div>
    </div>
</div>

<?php if (isset($order_data['special_instructions']) && !empty($order_data['special_instructions'])): ?>
<div class="order-detail-section">
    <div class="section-title">Special Instructions</div>
    <div class="detail-item">
        <div class="detail-value"><?php echo htmlspecialchars($order_data['special_instructions']); ?></div>
    </div>
</div>
<?php endif; ?>

<div class="order-detail-section">
    <div class="section-title">Status History</div>
    <?php if ($history_result->num_rows > 0): ?>
        <?php while ($history = $history_result->fetch_assoc()): ?>
            <div class="history-item">
                <div class="history-header">
                    <span class="history-status"><?php echo htmlspecialchars($history['status']); ?></span>
                    <span class="history-time"><?php echo date('M d, Y g:i A', strtotime($history['updated_at'])); ?></span>
                </div>
                <div class="history-by">
                    Updated by: <?php echo htmlspecialchars($history['updated_by_name'] ?: 'System'); ?>
                </div>
                <?php if (!empty($history['notes'])): ?>
                    <div class="history-notes"><?php echo htmlspecialchars($history['notes']); ?></div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="detail-item">
            <div class="detail-value">No status history available.</div>
        </div>
    <?php endif; ?>
</div>