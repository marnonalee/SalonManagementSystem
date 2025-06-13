<?php   
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrUser = $_POST['email_or_username'];
    $password    = $_POST['password'];

    if (strlen($emailOrUser) > 30) {
        $error = "Username or email must not exceed 30 characters.";
    } elseif (strlen($password) > 30) {
        $error = "Password must not exceed 30 characters.";
    } else {
        $admin_sql = "SELECT username, email, password, status FROM admins WHERE email = ? OR username = ?";
        $admin_stmt = $conn->prepare($admin_sql);
        $admin_stmt->bind_param("ss", $emailOrUser, $emailOrUser);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();

        if ($admin = $admin_result->fetch_assoc()) {
            if (strtolower($admin['status']) !== 'active') {
                $error = "Your admin account is not active.";
            } elseif (password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: admin/dashboard.php");
                exit();
            }
        }

        $employee_sql = "SELECT employee_id, name, email, password, status FROM employees WHERE email = ? OR name = ?";
        $employee_stmt = $conn->prepare($employee_sql);
        $employee_stmt->bind_param("ss", $emailOrUser, $emailOrUser);
        $employee_stmt->execute();
        $employee_result = $employee_stmt->get_result();

        if ($employee = $employee_result->fetch_assoc()) {
            if (strtolower($employee['status']) !== 'active') {
                $error = "Your employee account is not active.";
            } elseif (password_verify($password, $employee['password'])) {
                $_SESSION['employee_id'] = $employee['employee_id'];
                $_SESSION['employee'] = $employee['name'];
                $_SESSION['employee_email'] = $employee['email'];
                header("Location: employee/dashboard_emp.php");
                exit();
            }
        }

        $user_sql = "SELECT user_id, username, email, phone, password, status FROM users WHERE email = ? OR username = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("ss", $emailOrUser, $emailOrUser);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user = $user_result->fetch_assoc()) {
            if (strtolower($user['status']) !== 'active') {
                $error = "Your user account is not active.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                header("Location: user/services.php");
                exit();
            }
        }

        if (
            !$admin_result->num_rows &&
            !$employee_result->num_rows &&
            !$user_result->num_rows
        ) {
            $error = "No user found.";
        } elseif (empty($error)) {
            $error = "Incorrect password.";
        }

        $admin_stmt->close();
        $employee_stmt->close();
        $user_stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adore & Beauty - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="fixed top-0 left-0 w-full bg-white bg-opacity-20 backdrop-blur-md shadow-md z-50 py-3">
  <div class="max-w-7xl mx-auto flex items-center px-4">
    <div class="brand-logo">
      <img src="images/logo1.png" alt="Beauty & Style Logo" class="h-10 w-10">
    </div>
    <nav class="flex space-x-6 mx-auto">
      <a href="index.php#home" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-home"></i> Home
      </a>
      <a href="index.php#services" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-concierge-bell"></i> Our Services
      </a>
      <a href="index.php#contact" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
      <a href="guide.php"  class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-book"></i> Beauty & Style Guide
      </a>
    </nav>
    <div class="flex space-x-4">
      <a href="login.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-white font-semibold bg-gray-900 hover:bg-gray-700 transition duration-300 transform scale-105">
        Login
      </a>
      <a href="sign-in.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
        Signup
      </a>
    </div>
  </div>
</header>

<section class="flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <div class="flex justify-end">
      <a href="index.php" class="cursor-pointer">
        <i class="fas fa-times text-gray-500"></i>
      </a>
    </div>
    
    <h1 class="text-2xl font-bold text-center mb-4">Login to Your Account</h1>

    <?php if (!empty($error)): ?>
      <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="mb-4">
        <input type="text" name="email_or_username" placeholder="Enter Username or Email" maxlength="50" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-600" required>
      </div>
      <div class="mb-1 flex justify-between items-center">
        <span></span>
        <a href="forgot_password.php" class="text-sm text-teal-600 hover:underline">Forgot Password?</a>
      </div>
      <div class="mb-4">
        <input type="password" name="password" placeholder="Enter Password" maxlength="100" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-600" required>
      </div>
      <div class="mb-4">
        <button type="submit" class="block text-center w-full bg-[#004B49] text-white py-2 rounded-lg hover:bg-[#047857]">
          LOGIN
        </button>
      </div>
    </form>

    <p class="text-center text-gray-600">Don't have an account? 
      <a href="sign-in.php" class="text-red-600 hover:underline">Sign up</a>
    </p>
  </div>
</section>
</body>
</html>
