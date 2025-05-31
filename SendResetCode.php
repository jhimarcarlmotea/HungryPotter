<?php
require 'db.php';
require 'vendor/autoload.php'; // Import Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['email'] ?? $_GET['email'] ?? '';
$message = '';

if (empty($email)) {
    header("Location: ForgotPassword.php");
    exit;
}

// Check if email exists in database
$stmt = $conn->prepare("SELECT * FROM sign_up WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // For security, don't reveal that the email doesn't exist
    $message = "If your email exists in our system, a code has been sent.";
} else {
    // Generate a random 6-digit code
    $code = rand(100000, 999999);
    
    // Update the reset_code in the database
    $update = $conn->prepare("UPDATE sign_up SET reset_code = ? WHERE email = ?");
    $update->bind_param("ss", $code, $email);
    $update->execute();

    // Send email with code
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jhimarcarlmotea23@gmail.com'; // Your Gmail
        $mail->Password = 'jgap eope ducc poon';    // Your App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;     
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
        $mail->Subject = 'Password Reset Code - Hungry Potter';
        $mail->Body = "
            <h2>Password Reset</h2>
            <p>Hello,</p>
            <p>You requested a password reset for your Hungry Potter account.</p>
            <p>Your password reset code is: <b>$code</b></p>
            <p>This code will expire in 30 minutes.</p>
            <p>If you did not request this reset, please ignore this email.</p>
            <p>Best regards,<br>The Hungry Potter Team</p>
        ";

        $mail->send();
        $message = "✅ Password reset code sent to your email.";
    } catch (Exception $e) {
        $message = "❌ Error sending email: " . $mail->ErrorInfo;
    }
}

// Redirect to verification page
header("Location: VerifyResetCode.php?email=" . urlencode($email));
exit;
?>