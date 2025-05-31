<?php
require 'db.php';
session_start();

// Check if user role is set (for admin features)
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';

// Get the first name of the user from session
if (isset($_SESSION['first_name'])) {
    $firstName = $_SESSION['first_name'];
} else if (isset($_SESSION['user_name'])) {
    $fullName = $_SESSION['user_name'];
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
} else {
    $firstName = 'User';
}

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
    <link rel="stylesheet" href="style3.css" />
    <link rel="stylesheet" href="style4.css?v=<?php echo filemtime('style4.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Gallery - Hungry Potter</title>
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
        }
        
        .section-title:after {
            content: "";
            display: block;
            width: 100px;
            height: 3px;
            background: #8B0000;
            margin: 15px auto;
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
    </style>
</head>
<body>

    <?php if ($isAdmin): ?>
    <!-- Admin Panel - Only visible to admins -->
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
            <a href="#" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
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

  <div class="main-home" data-model-id="1467:605">
        <div class="overlap7">
            <div class="overlap-">
                <div class="overlap-">
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
    </div>

    <!-- Current Month Section -->
     <br><br><br><br><br><br><br><br><br><br><br><br>
    <div class="current-month-section">
        <h2 class="section-title">This Month's Pictures</h2>
        
        <div class="image-grid">
            <?php if (!empty($currentImages)): ?>
                <?php foreach ($currentImages as $image): ?>
                    <div class="image-container">
                        <img class="gallery-image" 
                             src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No images available for this month.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Last Month Section -->
    <div class="last-month-section">
        <h2 class="section-title">Last Month's Pictures</h2>
        
        <div class="image-grid">
            <?php if (!empty($lastMonthImages)): ?>
                <?php foreach ($lastMonthImages as $image): ?>
                    <div class="image-container">
                        <img class="gallery-image" 
                             src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No images available from last month.</p>
            <?php endif; ?>
        </div>
    </div>

      <br><br><br><br><br><br>

</body>
</html>
        <iframe src="https://www.cognitoforms.com/f/Pls_PdkOpE-8xdpog9lKTw/11" allow="payment" style="border:0;width:100%;" height="755" id="contacts"></iframe>
        <script src="https://www.cognitoforms.com/f/iframe.js"></script>