<?php
session_start();
include 'db.php';

$message = '';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_token'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_token = trim($_POST['token']);
    $stmt = $conn->prepare("SELECT user_id, user_type, token, expires FROM password_resets WHERE token = ? ORDER BY expires DESC LIMIT 1");
    $stmt->bind_param("s", $input_token);
    $stmt->execute();
    $stmt->bind_result($user_id, $user_type, $token, $expires);

    if ($stmt->fetch()) {
        $stmt->close();

        if ($token === $input_token && strtotime($expires) > time()) {
            $email_check = false;

            if ($user_type === 'user') {
                $check_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ? LIMIT 1");
                $check_stmt->bind_param("i", $user_id);
            } elseif ($user_type === 'admin') {
                $check_stmt = $conn->prepare("SELECT email FROM admins WHERE id = ? LIMIT 1");
                $check_stmt->bind_param("i", $user_id);
            } elseif ($user_type === 'employee') {
                $check_stmt = $conn->prepare("SELECT email FROM employees WHERE employee_id = ? LIMIT 1");
                $check_stmt->bind_param("i", $user_id);
            } else {
                $check_stmt = null;
            }

            if ($check_stmt) {
                $check_stmt->execute();
                $check_stmt->bind_result($db_email);
                if ($check_stmt->fetch()) {
                    if (strtolower($db_email) === strtolower($email)) {
                        $email_check = true;
                    }
                }
                $check_stmt->close();
            }

            if ($email_check) {
                $_SESSION['verified'] = true;
                $_SESSION['reset_user_id'] = $user_id;
                $_SESSION['reset_user_type'] = $user_type;

                header("Location: reset_password.php");
                exit();
            } else {
                $message = "Email does not match our records for this code.";
            }
        } else {
            $message = "Token is invalid or expired.";
        }
    } else {
        $message = "Invalid token.";
    }
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
      <a href="#home"  id="nav-home" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-home"></i> Home
      </a>
      <a href="#services"  id="nav-services" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-concierge-bell"></i> Our Services
      </a>
      <a href="#contact"  id="nav-contact"  class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
      <a href="guide.php"  class="flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
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
        <h1 class="text-2xl font-bold text-center mb-6">Verify Your Code</h1>

        <?php if ($message): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="verify_code.php" novalidate>
            <div class="mb-4">
            Enter the code sent to your email:
                <input type="text" name="token" placeholder="Please enter the code" class="w-full px-4 py-2 border rounded-lg" required/>
            </div>
            <button type="submit" class="w-full bg-teal-600 text-white p-2 rounded hover:bg-teal-700">Verify</button>
        </form>
        
    </div>
</section>
</body>
</html>

