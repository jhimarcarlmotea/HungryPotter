<?php
require 'db.php';

$email = $_GET['email'] ?? '';
$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $message = "❌ All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE sign_up SET password = ?, reset_code = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $message = "✅ Password reset successful! <a href='Login.php'>Log in</a>";
        } else {
            $message = "❌ Error updating password: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .error-message {
            color: #f44336;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
        }
        .success-message {
            color: #4CAF50;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
    </style>
</head>
<body>
  <div class="main-container">
    
    <div class="left-side">
      <div class="logo-section">
        <img src="logo.png" alt="Logo" class="logo">
        <div class="logo-text">
          <h2>HUNGRY POTTER</h2>
          <p>BEST TAPSILOGAN IN TOWN</p>
        </div>
      </div>
    </div>

    <div class="right-side">
      <div class="form-container">
        <h2>Reset Your Password</h2>
        
        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos($message, '✅') !== false ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        
        <form method="POST" action="UpdatePassword.php">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <label for="password">New Password</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            
            <label for="confirm_password">Confirm Password</label>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
            </div>
            
            <button type="submit">Update Password</button>
            
            <p class="login-link">Remember your password? <a href="Login.php">Log In</a></p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>