<?php
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

session_start();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        function findUserByEmail($conn, $table, $email, $id_field) {
            $stmt = $conn->prepare("SELECT $id_field FROM $table WHERE LOWER(email) = LOWER(?) LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id);
            if ($stmt->fetch()) {
                $stmt->close();
                return $id;
            }
            $stmt->close();
            return null;
        }

        $user_id = findUserByEmail($conn, 'users', $email, 'user_id');
        $user_type = 'user';

        if ($user_id === null) {
            $user_id = findUserByEmail($conn, 'admins', $email, 'id');
            $user_type = 'admin';
        }

        if ($user_id === null) {
            $user_id = findUserByEmail($conn, 'employees', $email, 'employee_id');
            $user_type = 'employee';
        }

        if ($user_id !== null) {
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $insert = $conn->prepare("INSERT INTO password_resets (user_id, user_type, token, expires) VALUES (?, ?, ?, ?)");
            $insert->bind_param("isss", $user_id, $user_type, $token, $expires);

            if ($insert->execute()) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'salonaandb@gmail.com';
                    $mail->Password = 'oqnblesgwkekaxcg';   
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('salonaandb@gmail.com', 'Adore & Beauty');
                    $mail->addAddress($email);
                    $mail->Subject = 'Password Reset Request';

                    $mail->Body = "You requested a password reset.\n\nYour verification code is: $token\n\nThis code will expire in 1 hour.";

                    $mail->send();

                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_token'] = $token;

                    header("Location: verify_code.php");
                    exit();

                } catch (Exception $e) {
                    $message = "Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $message = "An error occurred. Please try again later.";
            }
            $insert->close();
        } else {
            $message = "No account found with this email.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Adore & Beauty - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<header class="fixed top-0 left-0 w-full bg-white bg-opacity-20 backdrop-blur-md shadow-md z-50 py-3">
  <div class="max-w-7xl mx-auto flex items-center px-4">
    <div class="brand-logo">
      <img src="images/logo1.png" alt="Beauty & Style Logo" class="h-10 w-10">
    </div>
    <nav class="flex space-x-6 mx-auto">
      <a href="#home" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-home"></i> Home
      </a>
      <a href="#services" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-concierge-bell"></i> Our Services
      </a>
      <a href="#contact" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
      <a href="guide.php" class="flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-book"></i> Beauty & Style Guide
      </a>
    </nav>
    <div class="flex space-x-4">
      <a href="login.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
        Login
      </a>
      <a href="sign-in.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
        Signup
      </a>
    </div>
  </div>
</header>

<section class="flex items-center justify-center min-h-screen mt-10">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Forgot Password</h1>

        <?php if ($message): ?>
            <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST" novalidate>
            <div class="mb-4">
                Enter your email address:
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                />
            </div>
            <button type="submit" class="w-full bg-teal-600 text-white p-2 rounded hover:bg-teal-700">Send Verification Code</button>
        </form>

        <p class="mt-4 text-center text-gray-600 text-sm">
            Remember your password? <a href="login.php" class="text-teal-600 hover:underline">Login here</a>.
        </p>
    </div>
</section>
</body>
</html>
