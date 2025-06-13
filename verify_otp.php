<?php
include 'db.php';
session_start();

if (!isset($_SESSION['signup_data'])) {
    header("Location: sign-in.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = $_POST['otp'];
    if ($input_otp == $_SESSION['signup_data']['otp']) {
        $username = $_SESSION['signup_data']['username'];
        $email = $_SESSION['signup_data']['email'];
        $hashed_password = $_SESSION['signup_data']['password'];

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['signup_data']);
        header("Location: login.php");
        exit();
    } else {
        $error = "Invalid OTP code.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Adore & Beauty -  Verify OTP</title>
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
        <h1 class="text-2xl font-bold text-center mb-6">Verify OTP</h1>
    <form method="POST">
        <h2>Enter OTP sent to your email</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <input type="text" name="otp" placeholder="Please enter the code" class="w-full px-4 py-2 border rounded-lg" required/>
           
        <button type="submit">Verify</button>
    </form>
      
    </div>
</section>
</body>
</html>
