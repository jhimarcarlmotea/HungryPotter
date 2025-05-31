<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM sign_up WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Change this line to use password_verify for hashed passwords
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['userId'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
            
            // Always redirect to homepage, let homepage handle admin/user display
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not found.";
    }
    
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Now</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
    
    <div class="left-side">
      <div class="logo-section">
        <img src="Logo.png" alt="Logo" class="logo">
        <div class="logo-text">
          <h2>HUNGRY POTTER</h2>
          <p>BEST TAPSILOGAN IN TOWN</p>
        </div>
      </div>
    </div>

    <div class="right-side">
      <div class="form-container">
        <form action="Login.php" method="POST">
          <h2>Hi, Welcome! 👋</h2>
          <?php
            if (isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>";
            if (isset($_GET['signup_success'])) {
                echo "<p style='color: green; text-align:center;'>Sign Up Successful! Please log in.</p>";
            }
          ?>
          <label>Email address</label>
          <input name="email" type="email" placeholder="Your email" required>

          <label>Password</label>
          <div class="password-field">
            <input name="password" type="password" placeholder="Password" required>
            <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
          </div>

          <div class="forgot-password">
            <a href="ForgotPassword.php">Forgot password?</a>
          </div>

          <button type="submit">Sign in</button>

          <p class="signup-link">Don't have an account? <a href="SignUp.php">Register Now</a></p>
        </form>
      </div>
    </div>
  </div>
      <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>
</body>
</html>