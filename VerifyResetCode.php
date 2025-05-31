<?php
require 'db.php';

$email = $_GET['email'] ?? '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $input_code = $_POST['reset_code'];

    $stmt = $conn->prepare("SELECT reset_code FROM sign_up WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($input_code === $row['reset_code']) {
            // Clear code (optional for security)
            $clear = $conn->prepare("UPDATE sign_up SET reset_code = NULL WHERE email = ?");
            $clear->bind_param("s", $email);
            $clear->execute();

            // Redirect to reset password form
            header("Location: UpdatePassword.php?email=" . urlencode($email));
            exit;
        } else {
            $error = "Incorrect code. Please try again.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Reset Code</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .verification-code {
            letter-spacing: 5px;
            font-size: 18px;
            padding: 10px;
            width: 100%;
            margin: 20px auto;
            text-align: center;
        }
        .error-message {
            color: #f44336;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #ffebee;
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
        <h2>Verify Reset Code</h2>
        <p>We've sent a verification code to your email. Enter it below to continue.</p>
        
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <label>Verification Code</label>
            <input type="text" name="reset_code" class="verification-code" placeholder="Enter Code" maxlength="6" required>
            <button type="submit">Verify</button>
            
            <!-- Optional: Add resend code link -->
            <p class="login-link">Didn't receive the code? <a href="SendResetCode.php?email=<?= htmlspecialchars($email) ?>">Resend Code</a></p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>