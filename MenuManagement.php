<?php
session_start();

require 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $foodName = $conn->real_escape_string($_POST['foodName']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $category = $conn->real_escape_string($_POST['category'] === 'other' && !empty(trim($_POST['newCategory'])) ? trim($_POST['newCategory']) : $_POST['category']);
        $availability = $conn->real_escape_string($_POST['availability']);
        $description = $conn->real_escape_string($_POST['description']);
        $bestSeller = $conn->real_escape_string($_POST['best_seller']);
        
        // Handle image upload
        $imagePath = 'images/placeholder.jpg';
        
        if (isset($_FILES['foodImage']) && $_FILES['foodImage']['error'] === 0) {
            $uploadDir = 'Uploads/food/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['foodImage']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($fileType), $allowTypes)) {
                if (move_uploaded_file($_FILES['foodImage']['tmp_name'], $targetFilePath)) {
                    $imagePath = $targetFilePath;
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }
        
        // Fixed: Use correct column name 'bestSeller' instead of 'Best Seller'
        $stmt = $conn->prepare("INSERT INTO fooditems (foodName, image_path, price, quantity, category, availability, description, bestSeller) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiasss", $foodName, $imagePath, $price, $quantity, $category, $availability, $description, $bestSeller);
        
        if ($stmt->execute()) {
            $success = "New menu item added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'edit') {
        $foodId = intval($_POST['foodId']);
        $foodName = $conn->real_escape_string($_POST['foodName']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $category = $conn->real_escape_string($_POST['category'] === 'other' && !empty(trim($_POST['newCategory'])) ? trim($_POST['newCategory']) : $_POST['category']);
        $availability = $conn->real_escape_string($_POST['availability']);
        $description = $conn->real_escape_string($_POST['description']);
        $bestSeller = $conn->real_escape_string($_POST['best_seller']);
        
        if (isset($_FILES['foodImage']) && $_FILES['foodImage']['error'] === 0) {
            $uploadDir = 'Uploads/food/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['foodImage']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($fileType), $allowTypes)) {
                if (move_uploaded_file($_FILES['foodImage']['tmp_name'], $targetFilePath)) {
                    $oldImageQuery = "SELECT image_path FROM fooditems WHERE foodId = ?";
                    $stmt = $conn->prepare($oldImageQuery);
                    $stmt->bind_param("i", $foodId);
                    $stmt->execute();
                    $oldImageResult = $stmt->get_result();
                    if ($oldImageResult->num_rows > 0) {
                        $oldImageRow = $oldImageResult->fetch_assoc();
                        $oldImagePath = $oldImageRow['image_path'];
                        
                        if ($oldImagePath != 'images/placeholder.jpg' && file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $stmt->close();
                    
                    // Fixed: Use correct column name 'bestSeller'
                    $stmt = $conn->prepare("UPDATE fooditems SET foodName=?, image_path=?, price=?, quantity=?, category=?, availability=?, description=?, bestSeller=? WHERE foodId=?");
                    $stmt->bind_param("ssdissssi", $foodName, $targetFilePath, $price, $quantity, $category, $availability, $description, $bestSeller, $foodId);
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                    $stmt = $conn->prepare("UPDATE fooditems SET foodName=?, price=?, quantity=?, category=?, availability=?, description=?, bestSeller=? WHERE foodId=?");
                    $stmt->bind_param("sdissssi", $foodName, $price, $quantity, $category, $availability, $description, $bestSeller, $foodId);
                }
            } else {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $stmt = $conn->prepare("UPDATE fooditems SET foodName=?, price=?, quantity=?, category=?, availability=?, description=?, bestSeller=? WHERE foodId=?");
                $stmt->bind_param("sdissssi", $foodName, $price, $quantity, $category, $availability, $description, $bestSeller, $foodId);
            }
        } else {
            // Fixed: Use correct column name 'bestSeller'
            $stmt = $conn->prepare("UPDATE fooditems SET foodName=?, price=?, quantity=?, category=?, availability=?, description=?, bestSeller=? WHERE foodId=?");
            $stmt->bind_param("sdissssi", $foodName, $price, $quantity, $category, $availability, $description, $bestSeller, $foodId);
        }
        
        if ($stmt->execute()) {
            $success = "Menu item updated successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $foodId = intval($_POST['foodId']);
        
        $imageQuery = "SELECT image_path FROM fooditems WHERE foodId = ?";
        $stmt = $conn->prepare($imageQuery);
        $stmt->bind_param("i", $foodId);
        $stmt->execute();
        $imageResult = $stmt->get_result();
        if ($imageResult->num_rows > 0) {
            $imageRow = $imageResult->fetch_assoc();
            $imagePath = $imageRow['image_path'];
            
            if ($imagePath != 'images/placeholder.jpg' && file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM fooditems WHERE foodId=?");
        $stmt->bind_param("i", $foodId);
        
        if ($stmt->execute()) {
            $success = "Menu item deleted successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all menu items
$sql = "SELECT * FROM fooditems ORDER BY category, foodName";
$result = $conn->query($sql);

// Get unique categories
$categorySql = "SELECT DISTINCT category FROM fooditems ORDER BY category";
$categoryResult = $conn->query($categorySql);
$categories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hungry Potter - Menu Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
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
        
        .page-title {
            font-size: 32px;
            color: #2c3e50;
        }
        
        .add-btn {
            background-color: #27ae60;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }
        
        .add-btn:hover {
            background-color: #229954;
        }
        
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            position: relative;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-close {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            font-size: 18px;
        }
        
        .menu-table {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-container {
            overflow-x: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #8B0000;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .food-name {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .food-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .price {
            font-weight: 600;
            color: #27ae60;
        }
        
        .quantity {
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
        }
        
        .category {
            background-color: #3498db;
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .availability {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .availability.available {
            background-color: #27ae60;
            color: #fff;
        }
        
        .availability.not-available {
            background-color: #e74c3c;
            color: #fff;
        }
        
        .best-seller {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .best-seller.yes {
            background-color: #FFD700;
            color: #8B0000;
        }
        
        .best-seller.no {
            background-color: #95a5a6;
            color: #fff;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .edit-btn {
            background-color: #3498db;
            color: #fff;
        }
        
        .edit-btn:hover {
            background-color: #2980b9;
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }
        
        .delete-btn:hover {
            background-color: #c0392b;
        }
        
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
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .modal-header {
            background-color: #8B0000;
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }
        
        .close {
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #ddd;
        }
        
        .modal-body {
            padding: 20px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .image-preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .image-preview {
            width: 200px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #ddd;
        }
        
        .image-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
        }
        
        .image-upload-btn {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .image-upload-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .modal-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: #fff;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .confirm-dialog {
            text-align: center;
            padding: 20px;
        }
        
        .confirm-dialog i {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .confirm-dialog p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .search-bar {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .description-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
    </style>
</head>
<body>
    <!-- Admin Navigation Bar -->
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Category Tabs for Filtering -->
        <div class="category-tabs" style="margin-left: 0; margin-bottom: 20px;">
            <a href="MenuManagement.php" class="category-tab <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> All
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="MenuManagement.php?category=<?php echo urlencode($cat); ?>" class="category-tab <?php echo isset($_GET['category']) && $_GET['category'] == $cat ? 'active' : ''; ?>">
                    <i class="fas fa-utensils"></i> <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="page-header">
            <h1 class="page-title">Menu Management</h1>
            <button class="add-btn" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Item
            </button>
        </div>

        <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <span class="alert-close" onclick="this.parentElement.style.display='none'">×</span>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
            <span class="alert-close" onclick="this.parentElement.style.display='none'">×</span>
        </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" class="search-input" id="searchInput" placeholder="Search menu items...">
            <i class="fas fa-search search-icon"></i>
        </div>

        <!-- Menu Table -->
        <div class="menu-table">
            <div class="table-container">
                <?php
                // Modify query based on category filter
                $sql = "SELECT * FROM fooditems";
                if (isset($_GET['category']) && !empty($_GET['category'])) {
                    $selectedCategory = $conn->real_escape_string($_GET['category']);
                    $sql .= " WHERE category = '$selectedCategory'";
                }
                $sql .= " ORDER BY category, foodName";
                $result = $conn->query($sql);
                ?>
                <?php if ($result->num_rows > 0): ?>
                <table id="menuTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Food Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Availability</th>
                            <th>Description</th>
                            <th>Best Seller</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['foodId']; ?></td>
                            <td>
                                <img 
                                    src="<?php echo isset($row['image_path']) && !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : 'images/placeholder.jpg'; ?>" 
                                    alt="<?php echo htmlspecialchars($row['foodName']); ?>" 
                                    class="food-image"
                                    onerror="this.src='images/placeholder.jpg';"
                                >
                            </td>
                            <td class="food-name"><?php echo htmlspecialchars($row['foodName']); ?></td>
                            <td class="price">₱<?php echo number_format($row['price'], 2); ?></td>
                            <td class="quantity"><?php echo $row['quantity']; ?></td>
                            <td><span class="category"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td>
                                <span class="availability <?php echo strtolower(str_replace(' ', '-', $row['availability'])); ?>">
                                    <?php echo $row['availability']; ?>
                                </span>
                            </td>
                            <td class="description-cell" title="<?php echo htmlspecialchars($row['description'] ?? ''); ?>">
                                <?php echo htmlspecialchars($row['description'] ?? ''); ?>
                            </td>
                            <td>
                                <!-- Fixed: Use correct column name 'bestSeller' -->
                                <span class="best-seller <?php echo strtolower($row['bestSeller']); ?>">
                                    <?php echo $row['bestSeller']; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="action-btn edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $row['foodId']; ?>, '<?php echo htmlspecialchars($row['foodName']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    <h3>No Menu Items Found</h3>
                    <p>Start by adding your first menu item.</p>
                    <button class="add-btn" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="menuModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Item</h2>
                <span class="close" onclick="closeModal()">×</span>
            </div>
            <form method="POST" id="menuForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="foodId" id="foodId">
                    
                    <!-- Image Preview and Upload -->
                    <div class="image-preview-container">
                        <img id="imagePreview" src="images/placeholder.jpg" alt="Preview" class="image-preview">
                        <div class="image-upload-wrapper">
                            <div class="image-upload-btn">
                                <i class="fas fa-upload"></i> Upload Image
                            </div>
                            <input type="file" name="foodImage" id="foodImage" accept="image/*" onchange="previewImage(this)">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="foodName">Food Name *</label>
                        <input type="text" class="form-control" id="foodName" name="foodName" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="price">Price (₱) *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="quantity">Quantity Available *</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="category">Category *</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                            <option value="other">Add New Category</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="newCategoryGroup" style="display: none;">
                        <label class="form-label" for="newCategory">New Category Name *</label>
                        <input type="text" class="form-control" id="newCategory" name="newCategory">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="availability">Availability *</label>
                        <select class="form-control" id="availability" name="availability" required>
                            <option value="Available">Available</option>
                            <option value="Not Available">Not Available</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" placeholder="Enter food description here..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="best_seller">Best Seller *</label>
                        <select class="form-control" id="best_seller" name="best_seller" required>
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveFood()">Save</button>
                </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <span class="close" onclick="closeDeleteModal()">×</span>
            </div>
            <div class="modal-body">
                <div class="confirm-dialog">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p id="deleteMessage">Are you sure you want to delete this item?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="foodId" id="deleteFoodId">
                    <button type="submit" class="btn delete-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = 'images/placeholder.jpg';
            }
        }
        
        document.getElementById('category').addEventListener('change', function() {
            const newCategoryGroup = document.getElementById('newCategoryGroup');
            const newCategoryInput = document.getElementById('newCategory');
            if (this.value === 'other') {
                newCategoryGroup.style.display = 'block';
                newCategoryInput.setAttribute('required', 'required');
            } else {
                newCategoryGroup.style.display = 'none';
                newCategoryInput.removeAttribute('required');
                newCategoryInput.value = '';
            }
        });

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('formAction').value = 'add';
            document.getElementById('menuForm').reset();
            document.getElementById('imagePreview').src = 'images/placeholder.jpg';
            document.getElementById('newCategoryGroup').style.display = 'none';
            document.getElementById('newCategory').removeAttribute('required');
            document.getElementById('newCategory').value = '';
            document.getElementById('category').value = '';
            document.getElementById('best_seller').value = 'No';
            document.getElementById('menuModal').style.display = 'block';
        }

        function openEditModal(item) {
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('foodId').value = item.foodId;
            document.getElementById('foodName').value = item.foodName;
            document.getElementById('price').value = item.price;
            document.getElementById('quantity').value = item.quantity;
            document.getElementById('availability').value = item.availability;
            document.getElementById('description').value = item.description || '';
            // Fixed: Use correct property name 'bestSeller'
            document.getElementById('best_seller').value = item.bestSeller;
            
            const categorySelect = document.getElementById('category');
            const newCategoryGroup = document.getElementById('newCategoryGroup');
            const newCategoryInput = document.getElementById('newCategory');
            
            if (<?php echo json_encode($categories); ?>.includes(item.category)) {
                categorySelect.value = item.category;
                newCategoryGroup.style.display = 'none';
                newCategoryInput.removeAttribute('required');
                newCategoryInput.value = '';
            } else {
                categorySelect.value = 'other';
                newCategoryInput.value = item.category;
                newCategoryGroup.style.display = 'block';
                newCategoryInput.setAttribute('required', 'required');
            }
            
            const preview = document.getElementById('imagePreview');
            if (item.image_path && item.image_path !== 'images/placeholder.jpg') {
                preview.src = item.image_path;
            } else {
                preview.src = 'images/placeholder.jpg';
            }
            
            document.getElementById('menuModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('menuModal').style.display = 'none';
        }

        function confirmDelete(foodId, foodName) {
            document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${foodName}"?`;
            document.getElementById('deleteFoodId').value = foodId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        function saveFood() {
            const form = document.getElementById('menuForm');
            const categorySelect = document.getElementById('category');
            const newCategoryInput = document.getElementById('newCategory');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            if (categorySelect.value === 'other' && !newCategoryInput.value.trim()) {
                alert('Please enter a new category name');
                newCategoryInput.focus();
                return;
            }
            
            form.submit();
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('menuTable');
            if (!table) return;
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const foodName = rows[i].getElementsByClassName('food-name')[0];
                const category = rows[i].getElementsByClassName('category')[0];
                
                if (foodName && category) {
                    const textValue = foodName.textContent.toLowerCase();
                    const categoryValue = category.textContent.toLowerCase();
                    
                    if (textValue.includes(searchValue) || categoryValue.includes(searchValue)) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Prevent negative price
        document.getElementById('menuForm').addEventListener('submit', function(e) {
            const price = document.getElementById('price').value;
            if (price < 0) {
                e.preventDefault();
                alert('Price cannot be negative!');
            }
        });
    </script>
</body>
</html>