<?php
session_start();


// Check if user role is set (for admin features)
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';

// Get the first name of the user from session
if (isset($_SESSION['first_name'])) {
    // If first_name is already stored separately
    $firstName = $_SESSION['first_name'];
} else if (isset($_SESSION['user_name'])) {
    // If only full name is stored, extract the first part
    $fullName = $_SESSION['user_name'];
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0]; // Get only the first name
} else {
    // Fallback if no name is found
    $firstName = 'User';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hungry Potter - Home</title>
        <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="style2.css?v=<?php echo filemtime('style2.css'); ?>">
    <link rel="stylesheet" href="style2.css?v=<?php echo filemtime('AdminStyle.css'); ?>">
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
          .screenshot {
            width: 100%;
            transition: opacity 1s ease-in-out;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
                transform: scale(0.9); /* default zoomed out */
        }
.screenshot1 {
    width: 100%;
    transition: opacity 1s ease-in-out, transform 1s ease-in-out;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    transform: scale(0.9); /* zoomed out */
    border-radius: 30px;
    outline: 2px solid black; /* fixed: specify thickness and style */
}

        .active {
            opacity: 1;
        }
            .history-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #8B0000;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 18px;
            position: absolute;
            right: 150px;
            top: 25%;
            transform: translateY(-50%);
        }
        
        .history-icon:hover {
            background-color: #660000;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 4px 8px rgba(139, 0, 0, 0.3);
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
        <div class="div">
            <div class="overlap">
                <div class="frame"></div>
                <img class="aae-be-bb" src="https://c.animaapp.com/WuhV2pl3/img/aa6809e3-be41-4b3b-89c8-e0012ac24afe@2x.png" />
                <img class="element" src="https://c.animaapp.com/WuhV2pl3/img/228b6728-4377-4879-9a6c-b15e6adcf505@2x.png" />
                <img class="edad-aa-ba" src="https://c.animaapp.com/WuhV2pl3/img/ed4ad365-73aa-41ba-b50b-ccfd3581c8f2@2x.png" />
                <div class="our-menus">
                    <div class="overlap-group">
                        <div class="div-wrapper"><a class="text-wrapper" href="Menu.php">View Menu</a></div>
                        <div class="overlap-group-2">
                            <div class="text-wrapper-2">Our Menu's</div>
                            <img class="untitled-design" s src="https://c.animaapp.com/WuhV2pl3/img/untitled-design-removebg-preview-4@2x.png" />
                            <p class="discover-the">
                                "Discover the delicious world of silog at our shop, where we serve a variety of tasty meals made
                                with tender meats, perfectly cooked eggs, and savory garlic rice. Each dish is carefully prepared to
                                bring you the best combination of flavors, whether you're enjoying a classic longsilog, tocilog, or
                                something new. Our silog meals are not only satisfying but also perfect for any time of the day – from
                                breakfast to dinner. Every bite is packed with flavor, and we promise a fresh, hearty meal that will
                                keep you coming back for more. Whether you're here for a quick bite or a big meal, we've got something
                                for everyone!"
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overlap-2">
                <div class="frame-2"></div>
                <div class="about-us">
                    <div class="overlap-group-3">
                        <div class="abt-us-txt">
                            <p class="hungry-potter-kim-s">
                                Hungry Potter Kim's Tapsilogan, established in 2018, offers a unique dining experience with a
                                focus on great food and a welcoming atmosphere. Located on the rooftop, our dine-in area provides a
                                refreshing breeze and a scenic view, making every meal feel like a special occasion. Whether
                                you're enjoying a comforting tapsilog or exploring other local favorites, Hungry Potter is the
                                perfect spot to satisfy your cravings while enjoying the relaxing ambiance of our rooftop setting.
                            </p>
                        </div>
                        <div class="overlap-3">
                            <div class="text-wrapper-3">ABOUT US</div>
                            <img class="img" src="https://c.animaapp.com/WuhV2pl3/img/untitled-design-removebg-preview-2@2x.png" />
                        </div>
                        <div class="overlap-4">
                            <img class="image" src="https://c.animaapp.com/WuhV2pl3/img/image-48@2x.png" />
                            <img class="image-2" src="https://c.animaapp.com/WuhV2pl3/img/image-49@2x.png" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="overlap-5">
                <div class="frame-3"></div>
                <div class="locationt">
                    <div class="overlap-6">
                        <div class="gall-pic">
                            <img class="potter" src="https://c.animaapp.com/WuhV2pl3/img/potter-1@2x.png" />
                            <img class="element-2" src="https://c.animaapp.com/WuhV2pl3/img/462557308-8487259938047920-565602387957552794-n-1@2x.png" />
                            <img class="image-3" src="https://c.animaapp.com/WuhV2pl3/img/image-15@2x.png" />
                        </div>
                        <img class="maps" src="https://c.animaapp.com/WuhV2pl3/img/maps@2x.png" />
                        <img class="pngtree-red-location" src="https://c.animaapp.com/WuhV2pl3/img/pngtree-red-location-icon-sign-png-image-4644037-removebg-previe@2x.png" />
                        <p class="p">37-E A.Luna St. West Rembo Makati City 1215 Makati, Philippines</p>
                    </div>
                </div>
            </div>
    <div class="overlap-7">
        <div class="frame-4"></div>
        <img class="screenshot active" src="https://c.animaapp.com/WuhV2pl3/img/screenshot-2024-10-28-112105-1.png" />
        <img class="screenshot1 active" src="SS1.png" />
        <img class="screenshot1 active" src="SS2.png" />
        <div class="frame-wrapper">
            <div class="frame-5">
                <div class="frame-6"><div class="text-wrapper-4">HUNGRY POTTER</div></div>
                <div class="frame-7"><div class="text-wrapper-5">KIM'S TAPSILOGAN</div></div>
            </div>
        </div>
    </div>

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
 <?php if (isset($_SESSION['user_id'])): ?>
        <a href="History.php" class="history-icon" title="Order History">
            <i class="fas fa-history"></i>
        </a>
    <?php endif; ?>

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
            <img class="line-2" src="https://c.animaapp.com/WuhV2pl3/img/line-8.png" />
            <div class="overlap-11">
                <div class="text-wrapper-14">Hungry Potter</div>
                <img class="untitled-design-2" src="https://c.animaapp.com/WuhV2pl3/img/untitled-design-removebg-preview-3@2x.png" />
                <img class="line-3" src="https://c.animaapp.com/WuhV2pl3/img/line-5@2x.png" />
            </div>
        </div>
    </div>
    <iframe src="https://www.cognitoforms.com/f/Pls_PdkOpE-8xdpog9lKTw/11" allow="payment" style="border:0;width:100%;" height="755" id="contacts"></iframe>
    <script src="https://www.cognitoforms.com/f/iframe.js"></script>

      <script>
    let currentIndex = 0;
    // Tama ang selector, dapat gumamit ng querySelectorAll na may tamang comma
    const slides = document.querySelectorAll('.screenshot, .screenshot1');
    const totalSlides = slides.length;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        showSlide(currentIndex);
    }

    // Ipakita agad ang unang slide
    showSlide(currentIndex);

    // Awtomatikong magpalit ng slide kada 3 segundo
    setInterval(nextSlide, 3000);
</script>

</body>
</html>