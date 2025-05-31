<?php
require 'db.php';
session_start();

// Get the first name of the user from session
if (isset($_SESSION['first_name'])) {
    $firstName = $_SESSION['first_name'];
} else if (isset($_SESSION['user_name'])) {
    $fullName = $_SESSION['user_name'];
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
} else {
    $firstName = 'Guest';
}

// Initialize cart based on user status
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = "SELECT cart_data FROM user_carts WHERE user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cart_data = $result->fetch_assoc();
        $_SESSION['cart'] = json_decode($cart_data['cart_data'], true);
    } else {
        $_SESSION['cart'] = [];
    }
    $stmt->close();
} else {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// Check if user role is set (for admin features)
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';

// Fetch menu items from database with Best Seller flag
$sql = "SELECT foodId, foodName, image_path, price, quantity, category, availability, description, bestSeller 
        FROM fooditems 
        ORDER BY 
        CASE 
            WHEN bestSeller = 'Yes' THEN 1
            WHEN category = 'Silog Meal' THEN 2
            WHEN category = 'Hungry Hooray! & Wings and Dips' THEN 3
            WHEN category = 'Beverages & Extra' THEN 4
            WHEN category = 'Hungry Meals & Set Meals' THEN 5
            ELSE 6
        END,
        foodName ASC";
$result = $conn->query($sql);

$menuItems = [];
$bestSellerItems = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add to the respective category
        if ($row['category'] !== 'Best Sellers') {
            $menuItems[$row['category']][] = $row;
        }
        // Add to Best Sellers if flagged as such
        if ($row['bestSeller'] === 'Yes') {
            $bestSellerItems[] = $row;
        }
    }
}

// Add Best Sellers as a separate category if there are any
if (!empty($bestSellerItems)) {
    $menuItems['Best Sellers'] = $bestSellerItems;
}

// Reorder the menuItems array to match your desired sequence
$orderedMenuItems = [];
$categoryOrder = [
    'Best Sellers', 
    'Silog Meal', 
    'Hungry Hooray! & Wings and Dips',
    'Beverages & Extra',
    'Hungry Meals & Set Meals'
];

// Add categories in the specified order
foreach ($categoryOrder as $category) {
    if (isset($menuItems[$category])) {
        $orderedMenuItems[$category] = $menuItems[$category];
    }
}

// Add any remaining categories that weren't in our predefined order
foreach ($menuItems as $category => $items) {
    if (!isset($orderedMenuItems[$category])) {
        $orderedMenuItems[$category] = $items;
    }
}

// Replace the original $menuItems with the ordered version
$menuItems = $orderedMenuItems;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hungry Potter - Menu</title>
    <link rel="stylesheet" href="AdminStyle.css?v=<?php echo filemtime('AdminStyle.css'); ?>">
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
          overflow-x: hidden;
    }
    

    
    .category-tab.active,
    .category-tab:hover {
        background-color: #8B0000;
        color: #fff;
    }
    /* Menu Section */
    .menu-section{
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
    
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin-bottom: 50px;
    }
    
    .menu-item {
        background-color: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        display: flex;
        height: 200px;
        transition: transform 0.3s;
    }
    
    .menu-item:hover {
        transform: translateY(-5px);
    }
    
    .menu-item-image {
        width: 200px;
        height: 90%;
        object-fit: cover;
        border-radius:10px;
        margin-top:10px;
        margin-left:10px;
    }
    .menu-item-image2 {
        width: 100px;
        height: 80%;
        object-fit: cover;
    }
    
    .menu-item-details {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .menu-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }
    
    .menu-item-name {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }
    
    .menu-item-price {
        background-color: #333;
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 14px;
    }
    
    .menu-item-description {
        color: #666;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 15px;
    }
    
    .menu-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .quantity-control span {
        font-weight: bold;
    }
    
    .quantity-btn {
        width: 30px;
        height: 30px;
        border: none;
        background-color: #f0f0f0;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s;
    }
    
    .quantity-btn:hover {
        background-color: #e0e0e0;
    }
    
    .add-to-cart-btn {
        background-color: #e74c3c;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    
    .add-to-cart-btn:hover {
        background-color: #c0392b;
    }
    
    /* Floating Cart */
    .floating-cart {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background-color: #8B0000;
        color: #fff;
        width: 200px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        cursor: pointer;
        transition: transform 0.3s;
        z-index: 1000;
    }
    
    .floating-cart:hover {
        transform: scale(1.05);
    }
    
    .cart-header {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        font-weight: bold;
        font-size: 18px;
    }
    
    .cart-body {
        padding: 15px;
        text-align: center;
    }
    
    .cart-total {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .cart-items-count {
        font-size: 14px;
        opacity: 0.8;
    }
    
    /* Order Popup Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1001;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        overflow: auto;
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .modal-header {
        background-color: #8B0000;
        color: #fff;
        padding: 20px;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        position: relative;
    }
    
    .close {
        color: #fff;
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover {
        color: #f0f0f0;
    }
    
    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
        gap: 15px;
    }
    
    .order-item-image {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        object-fit: cover;
    }
    
    .order-item-details {
        flex: 1;
    }
    
    .order-item-name {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .order-item-price {
        color: #666;
        font-size: 14px;
    }
    
    .order-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-footer {
        background-color: #f8f8f8;
        padding: 20px;
        border-top: 1px solid #eee;
    }
    
    .order-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 24px;
        font-weight: bold;
    }
    
    .checkout-btn {
        width: 100%;
        background-color: #8B0000;
        color: #fff;
        padding: 15px;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .checkout-btn:hover {
        background-color: #6b0000;
    }
    .logout-btn {
        background-color: #e74c3c;
        color: #fff;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    
    .logout-btn:hover {
        background-color: #c0392b;
    }

     .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    
    .popup-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .popup-image {
        width: 100%;
        height: auto;
        max-width: 800px;
        max-height: 600px;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .close-button {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 30px;
        color: #ffffff;
        background-color: rgba(0, 0, 0, 0.5);
        border: none;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .close-button:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }
    
    .menu-item-image {
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    .menu-item-image:hover {
        transform: scale(1.05);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .menu-grid {
            grid-template-columns: 1fr;
        }
        
        .menu-item {
            flex-direction: column;
            height: auto;
        }
        
        .menu-item-image {
            width: 100%;
            height: 200px;
        }
        
        .floating-cart {
            width: 150px;
            bottom: 20px;
            right: 20px;
        }
        
        .modal-content {
            width: 95%;
            margin: 2% auto;
        }
    }
  
        /* Admin Panel Styling */
        .admin-panel {
            background-color: #ffffff;
            padding: 20px 0;
            border-bottom: 3px solid #8B0000;
            margin-bottom: 20px;
        }
        
        .category-tabs {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-left:250px
        }
                
        .category-tabs1 {
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
         .login-prompt {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            z-index: 10000;
            text-align: center;
        }
        .login-prompt-buttons {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .color{
            color:#fff;
        }
        /* Empty Cart Popup */
.empty-cart-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
    text-align: center;
    z-index: 1002;
    max-width: 350px;
    width: 90%;
}

.empty-cart-popup h3 {
    color: #8B0000;
    margin-bottom: 15px;
    font-size: 22px;
}

.empty-cart-popup p {
    margin-bottom: 20px;
    color: #555;
}

.empty-cart-popup button {
    background-color: #8B0000;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
}

.empty-cart-popup button:hover {
    background-color: #6B0000;
}
.best-seller-badge {
    background-color: #FFD700;
    color: #8B0000;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
    display: inline-block;
}
    </style>
</head>
<body>
    <?php if ($isAdmin): ?>
    <!-- Admin Panel - Only visible to admins -->
    <div class="admin-panel">
        <div class="category-tabs1">
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
    <?php endif; ?>
    
    <!-- Main Navigation -->
    <div class="main-home" data-model-id="1467:605">
        <div class="overlap7">
            <div class="overlap-8">
                <div class="overlap-9">
                    <a href="index.php">
                        <img class="logo" src="https://c.animaapp.com/WuhV2pl3/img/logo@2x.png" />
                        <img class="line" src="https://c.animaapp.com/WuhV2pl3/img/line-4.png" />
                    </a>
                </div>
                <div class="overlap-10">
                    <div class="logo-fb-simple-wrapper"><div class="logo-fb-simple"></div></div>
                    <div class="text-wrapper-6">Hungry Potter Kim's Tapsilogan</div>
                    <p class="text-wrapper-7">Monday - Sunday 7:00 Am - 11:00 Pm</p>
                </div>
                <div class="text-wrapper-8">BEST TAPSILOGAN IN TOWN</div>
                <div class="text-wrapper-9">HUNGRY POTTER</div>
                <a href="index.php">
                    <div class="menus">
                        <div class="text-wrapper-10">HOME</div>
                    </div>
                </a>
                <a href="Menu.php">
                    <div class="menus-2">
                        <div class="text-wrapper-11">MENUS</div>
                    </div>
                </a>
                <a href="gallery.php">
                    <div class="gallery">
                        <div class="text-wrapper-12">GALLERY</div>
                    </div>
                </a>
                <a href="#contacts">
                    <div class="contacts">
                        <div class="text-wrapper-13">CONTACT</div>
                    </div>
                </a>
                <div class="welcome-container">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <p class="welcome-text">
                            Welcome, <?php echo htmlspecialchars($firstName); ?>!
                        </p>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    <?php else: ?>
                        <a href="Login.php" class="login-btn">Log In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <br><br><br><br><br><br><br><br><br><br>
        
<div class="category-tabs">
    <button class="category-tab active" onclick="filterCategory('all')">All Items</button>
    <?php
    // Define the complete category order as you want it to appear
    $categoryOrder = [
        'Best Sellers', 
        'Silog Meals', 
        'Hungry Hooray! & Wings and Dips',
        'Hungry Meals & Set Meals',
        'Beverages & Extra'
    ];
    
    // Add any additional categories that might exist but aren't in our predefined order
    $allCategories = array_keys($menuItems);
    foreach ($allCategories as $category) {
        if (!in_array($category, $categoryOrder)) {
            $categoryOrder[] = $category;
        }
    }
    
    // Render tabs in the defined order
    foreach ($categoryOrder as $category):
        if (isset($menuItems[$category])): // Ensure the category exists
    ?>
        <button class="category-tab" onclick="filterCategory('<?php echo htmlspecialchars($category); ?>')"><?php echo htmlspecialchars($category); ?></button>
    <?php endif; endforeach; ?>
</div>


        <!-- Menu Section -->
        <div class="menu-section">
            <?php foreach($menuItems as $category => $items): ?>
            <div class="category-section" data-category="<?php echo htmlspecialchars($category); ?>">
                <div class="category-title">
                    <h2><?php echo htmlspecialchars($category); ?></h2>
                    <p><?php echo $category === 'Best Sellers' ? 'Our Most Popular Dishes' : 'Delicious Filipino favorites'; ?></p>
                </div>
                
                <div class="menu-grid">
                    <?php foreach($items as $item): ?>
                    <div class="menu-item">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($item['foodName']); ?>" 
                             class="menu-item-image"
                             onclick="openPopup('<?php echo htmlspecialchars($item['image_path']); ?>', '<?php echo htmlspecialchars($item['foodName']); ?>')">
                        <div class="menu-item-details">
                            <div class="menu-item-header">
                                <h3 class="menu-item-name">
                                    <?php echo htmlspecialchars($item['foodName']); ?>
                                    <?php if ($item['bestSeller'] === 'Yes'): ?>
                                        <span class="best-seller-badge">Best Seller</span>
                                    <?php endif; ?>
                                </h3>
                                <span class="menu-item-price">₱<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                            <p class="menu-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <p class="menu-item-quantity">Available: <?php echo $item['quantity']; ?> left</p>
                            <div class="menu-item-footer">
                                <div class="quantity-control">
                                    <span>Quantity:</span>
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['foodId']; ?>, -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span id="quantity-<?php echo $item['foodId']; ?>">1</span>
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['foodId']; ?>, 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <?php if ($item['quantity'] > 0 && $item['availability'] === 'Available'): ?>
                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $item['foodId']; ?>, '<?php echo htmlspecialchars($item['foodName']); ?>', <?php echo $item['price']; ?>, '<?php echo htmlspecialchars($item['image_path']); ?>', '<?php echo htmlspecialchars($item['description']); ?>', <?php echo $item['quantity']; ?>)">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button class="add-to-cart-btn" disabled style="background-color: #ccc; cursor: not-allowed;">
                                        Unavailable
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Floating Cart -->
        <div class="floating-cart" onclick="openOrderModal()">
            <div class="cart-header">
                <i class="fas fa-shopping-cart"></i> My Order
            </div>
            <div class="cart-body">
                <div class="cart-total">₱<span id="cart-total">0</span></div>
                <div class="cart-items-count">
                    <span id="cart-count">0</span> items
                </div>
            </div>
        </div>
        
        <!-- Order Modal -->
        <div id="orderModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    My Order
                    <span class="close" onclick="closeOrderModal()">×</span>
                </div>
                <div class="modal-body" id="order-items-list">
                    <!-- Order items will be dynamically added here -->
                </div>
                <div class="modal-footer">
                    <div class="order-total">
                        <span>Total:</span>
                        <span>₱<span id="modal-total">0</span></span>
                    </div>
                    <button onclick="checkout()" class="checkout-btn">Check Out</button>
                </div>
            </div>
        </div>
        
        <div id="imagePopup" class="popup-overlay">
            <div class="popup-content">
                <button class="close-button" onclick="closePopup()">×</button>
                <img id="popupImage" class="popup-image" src="" alt="">
                <div class="food-info">
                    <div id="foodName" class="food-name"></div>
                </div>
            </div>
            <div id="loginPrompt" class="login-prompt">
                <h3>Login Required</h3>
                <p>You need to be logged in to add items to your cart.</p>
                <div class="login-prompt-buttons">
                    <button onclick="window.location.href='Login.php'">Login</button>
                    <button onclick="document.getElementById('loginPrompt').style.display='none'">Cancel</button>
                </div>
            </div>
        </div>
        
        <!-- Empty Cart Popup -->
        <div id="emptyCartPopup" class="empty-cart-popup">
            <h3>Your Cart is Empty</h3>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <button onclick="closeEmptyCartPopup()">Continue Shopping</button>
        </div>
        
        <iframe src="https://www.cognitoforms.com/f/Pls_PdkOpE-8xdpog9lKTw/11" allow="payment" style="border:0;width:100%;" height="755" id="contacts"></iframe>
        <script src="https://www.cognitoforms.com/f/iframe.js"></script>
        
        <script>
            let cart = <?= json_encode($_SESSION['cart']) ?>;
            const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
            
            // Category filtering function
            function filterCategory(category) {
                const sections = document.querySelectorAll('.category-section');
                const tabs = document.querySelectorAll('.category-tab');
                
                tabs.forEach(tab => tab.classList.remove('active'));
                event.currentTarget.classList.add('active');
                
                if (category === 'all') {
                    sections.forEach(section => {
                        section.style.display = 'block';
                    });
                } else {
                    sections.forEach(section => {
                        if (section.getAttribute('data-category') === category) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }
            }
            
            function showLoginPrompt() {
                document.getElementById('loginPrompt').style.display = 'flex';
            }
            
            function hideLoginPrompt() {
                document.getElementById('loginPrompt').style.display = 'none';
            }
            
            function updateQuantity(foodId, change) {
                const quantityElement = document.getElementById(`quantity-${foodId}`);
                const currentQuantity = parseInt(quantityElement.textContent);
                const availableQuantity = parseInt(document.querySelector(`#quantity-${foodId}`).parentElement.parentElement.parentElement.querySelector('.menu-item-quantity').textContent.match(/\d+/)[0]);
    
                const newQuantity = currentQuantity + change;
    
                if (newQuantity < 1) {
                    quantityElement.textContent = 1;
                    alert('Quantity cannot be less than 1.');
                } else if (newQuantity > availableQuantity) {
                    quantityElement.textContent = currentQuantity;
                    alert(`Cannot select more than ${availableQuantity} items. Only ${availableQuantity} available.`);
                } else {
                    quantityElement.textContent = newQuantity;
                }
            }
            
            function addToCart(foodId, foodName, price, imagePath, description, availableQuantity) {
                if (!isLoggedIn) {
                    showLoginPrompt();
                    return;
                }
    
                const quantityElement = document.getElementById(`quantity-${foodId}`);
                const requestedQuantity = parseInt(quantityElement.textContent);
    
                if (requestedQuantity > availableQuantity) {
                    alert(`Sorry, only ${availableQuantity} ${foodName} available in stock.`);
                    return;
                }
    
                const existingItem = cart.find(item => item.foodId == foodId);
    
                if (existingItem) {
                    const newQuantity = existingItem.quantity + requestedQuantity;
                    if (newQuantity > availableQuantity) {
                        alert(`Cannot add ${requestedQuantity} more ${foodName}. Only ${availableQuantity} available.`);
                        return;
                    }
                    existingItem.quantity = newQuantity;
                } else {
                    cart.push({
                        foodId: foodId,
                        foodName: foodName,
                        price: parseFloat(price),
                        quantity: requestedQuantity,
                        image_path: imagePath,
                        description: description
                    });
                }
    
                updateCartDisplay();
                saveCart();
                quantityElement.textContent = 1;
                alert(`${requestedQuantity} ${foodName} added to cart!`);
            }
            
            function saveCart() {
                const endpoint = isLoggedIn ? 'save_cart.php' : 'update_cart.php';
                
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(isLoggedIn ? { cart: cart } : cart)
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to save cart:', data.message);
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving cart:', error);
                });
            }
            
            function updateCartDisplay() {
                let total = 0;
                let itemCount = 0;
                
                cart.forEach(item => {
                    total += item.price * item.quantity;
                    itemCount += item.quantity;
                });
                
                document.getElementById('cart-total').textContent = total.toFixed(2);
                document.getElementById('cart-count').textContent = itemCount;
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                updateCartDisplay();
            });
            
            function openOrderModal() {
                const modal = document.getElementById('orderModal');
                const orderItemsList = document.getElementById('order-items-list');
                orderItemsList.innerHTML = '';
    
                if (cart.length === 0) {
                    orderItemsList.innerHTML = '<p style="text-align: center; padding: 40px;">Your cart is empty</p>';
                } else {
                    cart.forEach(item => {
                        const orderItem = document.createElement('div');
                        orderItem.className = 'order-item';
                        orderItem.innerHTML = `
                            <img src="${item.image_path}" alt="${item.foodName}" class="menu-item-image2">
                            <div class="order-item-details">
                                <div class="order-item-name">${item.foodName}</div>
                                <div class="order-item-price">₱${item.price.toFixed(2)} each</div>
                            </div>
                            <div class="order-item-quantity">
                                <button class="quantity-btn" onclick="updateCartItem(${item.foodId}, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span>${item.quantity}</span>
                                <button class="quantity-btn" onclick="updateCartItem(${item.foodId}, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="quantity-btn" onclick="removeFromCart(${item.foodId})" style="margin-left: 10px; background-color: #e74c3c; color: white;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                        orderItemsList.appendChild(orderItem);
                    });
                }
    
                updateModalTotal();
                modal.style.display = 'block';
            }
            
            function closeOrderModal() {
                document.getElementById('orderModal').style.display = 'none';
            }
            
            function updateCartItem(foodId, change) {
                const item = cart.find(item => item.foodId === foodId);
                
                if (item) {
                    item.quantity += change;
    
                    if (item.quantity <= 0) {
                        const confirmDelete = confirm(`Do you want to delete ${item.foodName} from your cart?`);
                        if (confirmDelete) {
                            cart = cart.filter(i => i.foodId !== foodId);
                        } else {
                            item.quantity = 1;
                        }
                    }
    
                    openOrderModal();
                    updateCartDisplay();
                    saveCart();
                }
            }
            
            function removeFromCart(foodId) {
                cart = cart.filter(item => item.foodId !== foodId);
                openOrderModal();
                updateCartDisplay();
                saveCart();
            }
            
            function updateModalTotal() {
                let total = 0;
                cart.forEach(item => {
                    total += item.price * item.quantity;
                });
                document.getElementById('modal-total').textContent = total.toFixed(2);
            }
            
            function checkout() {
                if (cart.length === 0) {
                    showEmptyCartPopup();
                    return;
                }
                
                window.location.href = 'checkout.php';
            }
            
            function showEmptyCartPopup() {
                document.getElementById('emptyCartPopup').style.display = 'block';
            }
            
            function closeEmptyCartPopup() {
                document.getElementById('emptyCartPopup').style.display = 'none';
            }
            
            function openPopup(imageSrc, foodName) {
                const popup = document.getElementById('imagePopup');
                const popupImage = document.getElementById('popupImage');
                const foodNameElement = document.getElementById('foodName');
                
                popupImage.src = imageSrc;
                popupImage.alt = foodName;
                foodNameElement.textContent = foodName;
                
                popup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
            
            function closePopup() {
                const popup = document.getElementById('imagePopup');
                popup.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            document.getElementById('imagePopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closePopup();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closePopup();
                }
            });
        </script>
    </body>
</html>