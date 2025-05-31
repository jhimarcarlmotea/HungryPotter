<?php
require 'db.php';
require 'vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Function to send verification code
function sendVerificationCode($email, $firstName, $code) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jhimarcarlmotea23@gmail.com'; // Replace with your Gmail
        $mail->Password = 'rtfz tcow tklk pbom';    // Replace with your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

                $mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
        // Sender and recipient
        $mail->setFrom('jhimarcarlmotea23@gmail.com', 'Hungry Potter');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Hungry Potter';
        $mail->Body = "
            <h2>Welcome to Hungry Potter, $firstName!</h2>
            <p>Thank you for signing up. Please verify your email address to complete your registration.</p>
            <p>Your verification code is: <b>$code</b></p>
            <p>This code will expire in 30 minutes.</p>
            <p>Best regards,<br>The Hungry Potter Team</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
    echo("PHPMailer Error: " . $e->getMessage()); // Log the error
    return false;
}
}

// Step 1: Collect user information
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['verification_code'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate passwords match
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT * FROM sign_up WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            // Generate verification code
            $verificationCode = rand(100000, 999999);
            
            // Store user data in session
            $_SESSION['signup_data'] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'address' => $address,
                'phoneNumber' => $phoneNumber,
                'email' => $email,
                'password' => $password,
                'verification_code' => $verificationCode,
                'code_expires' => time() + 1800 // 30 minutes
            ];
            
            // Send verification code 
            if (sendVerificationCode($email, $firstName, $verificationCode)) {
                $verificationSent = true;
            } else {
                $error = "Failed to send verification email. Please try again.";
                echo var_dump($email, $firstName, $verificationCode);
            }
        }
    }
}

// Step 2: Verify code and complete registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    $inputCode = $_POST['verification_code'];
    
    if (!isset($_SESSION['signup_data'])) {
        $error = "Session expired. Please start the registration process again.";
    } else {
        $data = $_SESSION['signup_data'];
        
        // Check if code is expired
        if (time() > $data['code_expires']) {
            $error = "Verification code has expired. Please restart the registration.";
            unset($_SESSION['signup_data']);
        } 
        // Verify code
        else if ($inputCode == $data['verification_code']) {
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO sign_up (firstName, lastName, address, phoneNumber, email, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", 
                $data['firstName'], 
                $data['lastName'], 
                $data['address'], 
                $data['phoneNumber'], 
                $data['email'], 
                $hashedPassword
            );
            
            if ($stmt->execute()) {
                // Clear session data
                unset($_SESSION['signup_data']);
                
                // Redirect to login page
                header("Location: Login.php?signup_success=true");
                exit;
            } else {
                $error = "Error creating account: " . $stmt->error;
            }
        } else {
            $error = "Incorrect verification code. Please try again.";
        }
    }
}

// Resend verification code
if (isset($_GET['resend']) && isset($_SESSION['signup_data'])) {
    $data = $_SESSION['signup_data'];
    
    // Generate new code
    $newCode = rand(100000, 999999);
    $_SESSION['signup_data']['verification_code'] = $newCode;
    $_SESSION['signup_data']['code_expires'] = time() + 1800; // 30 minutes
    
    // Send new code
    if (sendVerificationCode($data['email'], $data['firstName'], $newCode)) {
        $resendSuccess = true;
    } else {
        $error = "Failed to resend verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Sign Up</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .verification-container {
      max-width: 400px;
      margin: 0 auto;
      padding: 20px;
      text-align: center;
    }
    .verification-code {
      letter-spacing: 5px;
      font-size: 18px;
      padding: 10px;
      width: 150px;
      margin: 20px auto;
      text-align: center;
    }
    .success-message {
      color: green;
      margin: 10px 0;
    }
    .resend-link {
      margin-top: 15px;
      display: block;
      color: #555;
      text-decoration: underline;
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
        <?php if (isset($error)): ?>
          <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if (isset($resendSuccess)): ?>
          <p class="success-message">Verification code resent successfully!</p>
        <?php endif; ?>
        
        <?php if (isset($verificationSent) && $verificationSent): ?>
          <!-- Email Verification Form -->
          <div class="verification-container">
            <h2>Verify Your Email</h2>
            <p>We've sent a verification code to <strong><?php echo htmlspecialchars($_SESSION['signup_data']['email']); ?></strong></p>
            
            <form action="SignUp.php" method="POST">
              <input type="text" name="verification_code" class="verification-code" placeholder="Enter code" maxlength="6" required>
              <button type="submit">Verify & Create Account</button>
            </form>
            
            <a href="SignUp.php?resend=true" class="resend-link">Didn't receive a code? Resend</a>
          </div>
        <?php else: ?>
          <!-- Regular Sign Up Form -->
          <form action="SignUp.php" method="POST">
            <h2>Create Account</h2>
            
            <div class="name-container">
              <input name="firstName" placeholder="First Name" value="<?php echo isset($_SESSION['signup_data']) ? htmlspecialchars($_SESSION['signup_data']['firstName']) : ''; ?>" required>
              <input name="lastName" placeholder="Last Name" value="<?php echo isset($_SESSION['signup_data']) ? htmlspecialchars($_SESSION['signup_data']['lastName']) : ''; ?>" required>
            </div>
            
            <input name="address" placeholder="Address" class="add" value="<?php echo isset($_SESSION['signup_data']) ? htmlspecialchars($_SESSION['signup_data']['address']) : ''; ?>" required>
            <input name="phoneNumber" placeholder="Phone Number" class="add" value="<?php echo isset($_SESSION['signup_data']) ? htmlspecialchars($_SESSION['signup_data']['phoneNumber']) : ''; ?>" required>
            <input name="email" type="email" placeholder="Email" class="add" value="<?php echo isset($_SESSION['signup_data']) ? htmlspecialchars($_SESSION['signup_data']['email']) : ''; ?>" required>
            
            <div class="password-container">
              <input name="password" type="password" id="password" placeholder="Password" required>
              <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            
            <div class="password-container">
              <input name="confirmPassword" type="password" id="confirmPassword" placeholder="Confirm Password" required>
              <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
            </div>
            
            <button type="submit">Sign Up</button>
            
            <p class="login-link">Already have an account? <a href="Login.php">Log In</a></p>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>