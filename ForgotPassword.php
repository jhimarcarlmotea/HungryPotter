<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <form method="POST" action="SendResetCode.php">
          <h2>Forgot Password</h2>
          <p>Enter your email address and we'll send you a code to reset your password.</p>
          
          <?php if (isset($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
          <?php endif; ?>
          
          <label>Email address</label>
          <input type="email" name="email" placeholder="Your email" required><br>
          
          <br>
          <button type="submit">Send Reset Code</button>
          
          <p class="login-link">Remember your password? <a href="Login.php">Log In</a></p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>