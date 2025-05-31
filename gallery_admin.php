<?php
require 'db.php';
session_start();

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';

if (!$isAdmin) {
    header("Location: gallery.php");
    exit();
}

// Define the getGalleryImages function
function getGalleryImages($category, $conn) {
    $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE category = ? ORDER BY display_order ASC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];
    
    while($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    $stmt->close();
    return $images;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $title = $conn->real_escape_string($_POST['title']);
        $category = $conn->real_escape_string($_POST['category']);
        $display_order = intval($_POST['display_order']);
        
        // Handle image upload
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === 0) {
            $uploadDir = 'uploads/gallery/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['gallery_image']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            // Allow certain file formats
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array(strtolower($fileType), $allowTypes)) {
                // Upload file to server
                if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $targetFilePath)) {
                    // Insert into database with image path
                    $sql = "INSERT INTO gallery_images (title, category, image_path, display_order) 
                            VALUES ('$title', '$category', '$targetFilePath', $display_order)";
                    
                    if ($conn->query($sql) === TRUE) {
                        $success = "New image added successfully!";
                    } else {
                        $error = "Error: " . $conn->error;
                    }
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $error = "Please select an image file to upload.";
        }
    } 
    elseif ($action === 'delete') {
        $imageId = intval($_POST['image_id']);
        
        // Get image path to delete the file
        $imageQuery = "SELECT image_path FROM gallery_images WHERE id = $imageId";
        $imageResult = $conn->query($imageQuery);
        if ($imageResult->num_rows > 0) {
            $imageRow = $imageResult->fetch_assoc();
            $imagePath = $imageRow['image_path'];
            
            // Delete the image file if it exists
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $sql = "DELETE FROM gallery_images WHERE id=$imageId";
        
        if ($conn->query($sql) === TRUE) {
            $success = "Image deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Get current month images
$currentImages = getGalleryImages('current', $conn);

// Get last month images
$lastMonthImages = getGalleryImages('last_month', $conn);

// Close the database connection when done
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals1.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Gallery Management - Hungry Potter</title>
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
            margin-top: <?php echo $isAdmin ? '60px' : '0'; ?>;
        }
        
        /* Gallery Header Styles */
        .gallery-header {
            text-align: center;
            padding: 40px 0;
            position: relative;
        }
        
        .gallery-logo {
            width: 200px;
            margin-bottom: 20px;
        }
        
        .gallery-title {
            font-family: "Oleo Script", Helvetica;
            font-size: 48px;
            color: #8B0000;
            margin-bottom: 10px;
        }
        
        /* Section Titles */
        .section-title {
            font-family: "Oleo Script", Helvetica;
            font-size: 36px;
            color: #8B0000;
            text-align: center;
            margin: 40px 0 30px;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-title:after {
            content: "";
            display: block;
            width: 100px;
            height: 3px;
            margin: 15px auto;
        }
        
        .add-image-btn {
            background-color: #27ae60;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
            margin-left:600px
        }
        
        .add-image-btn:hover {
            background-color: #229954;
        }
        
        /* Image Grid */
        .image-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .image-container {
            width: 350px;
            height: 300px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .image-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            padding: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .image-container:hover .image-actions {
            opacity: 1;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }
        
        .delete-btn:hover {
            background-color: #c0392b;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            position: relative;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
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
        
        /* Modal */
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
        
        /* Image preview styles */
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
        
        /* Confirm Dialog */
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
        
        /* Navigation Styles */
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
        
        .welcome-container {
            position: absolute;
            width: max-content;
            height: 43px;
            top: 19px;
            left: 905px;
            border-radius: 20px;
            border: 2px solid #980000;
            display: flex;
            align-items: center;
            padding: 0 15px;
            gap: 15px;
            white-space: nowrap;
            overflow: hidden;
            z-index: 10;
        }
        
        .welcome-text {
            font-family: "Lato", Helvetica;
            font-weight: 800;
            color: #6e0606;
            font-size: 16px;
            margin: 0;
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

    <!-- Admin Panel -->
    <div class="admin-panel">
        <div class="category-tabs">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </div>
            <a href="Manageusers.php" class="category-tab">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="gallery_admin.php" class="category-tab active">
                <i class="fas fa-images"></i> Gallery
            </a>
            <a href="orders.php" class="category-tab">
                <i class="fas fa-box"></i> Orders
            </a>
            <a href="MenuManagement.php" class="category-tab">
                <i class="fas fa-utensils"></i> Menu Management
            </a>
                    <a href="Returns.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'manage_returns.php' ? 'active' : ''; ?>">
            <i class="fas fa-undo"></i> Manage Returns
        </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="menu-section">
        <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <span class="alert-close" onclick="this.parentElement.style.display='none'">&times;</span>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
            <span class="alert-close" onclick="this.parentElement.style.display='none'">&times;</span>
        </div>
        <?php endif; ?>

        <!-- Current Month Section -->
        <div class="current-month-section">
            <h2 class="section-title">
                This Month's Pictures
                <button class="add-image-btn" onclick="openAddModal('current')">
                    <i class="fas fa-plus"></i> Add Image
                </button>
            </h2>
            
            <div class="image-grid">
                <?php if (!empty($currentImages)): ?>
                    <?php foreach ($currentImages as $image): ?>
                        <div class="image-container">
                            <img class="gallery-image" 
                                 src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['title']); ?>">
                            <div class="image-actions">
                                <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $image['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No images available for this month.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Last Month Section -->
        <div class="last-month-section">
            <h2 class="section-title">
                Last Month's Pictures
                <button class="add-image-btn" onclick="openAddModal('last_month')">
                    <i class="fas fa-plus"></i> Add Image
                </button>
            </h2>
            
            <div class="image-grid">
                <?php if (!empty($lastMonthImages)): ?>
                    <?php foreach ($lastMonthImages as $image): ?>
                        <div class="image-container">
                            <img class="gallery-image" 
                                 src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['title']); ?>">
                            <div class="image-actions">
                                <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $image['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No images available from last month.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Image</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST" id="addForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="category" id="imageCategory">
                    
                    <!-- Image Preview and Upload -->
                    <div class="image-preview-container">
                        <img id="imagePreview" src="images/placeholder.jpg" alt="Preview" class="image-preview">
                        <div class="image-upload-wrapper">
                            <div class="image-upload-btn">
                                <i class="fas fa-upload"></i> Upload Image
                            </div>
                            <input type="file" name="gallery_image" id="galleryImage" accept="image/*" onchange="previewImage(this)" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="title">Image Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="display_order">Display Order *</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="confirm-dialog">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Are you sure you want to delete this image?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="image_id" id="deleteImageId">
                    <button type="submit" class="btn delete-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>

    <script>
        // Image preview function
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
        
        // Modal functions
        function openAddModal(category) {
            document.getElementById('imageCategory').value = category;
            document.getElementById('addForm').reset();
            document.getElementById('imagePreview').src = 'images/placeholder.jpg';
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function confirmDelete(imageId) {
            document.getElementById('deleteImageId').value = imageId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>