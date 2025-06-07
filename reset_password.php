<?php 
session_start();
include 'db.php';

$message = '';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['verified'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    $errors = [];

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if (strlen($password) > 30) {
        $errors[] = "Password must not exceed 30 characters.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $updated = false;

        function updatePassword($conn, $table, $email, $password) {
            $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE LOWER(email) = LOWER(?)");
            $stmt->bind_param("ss", $password, $email);
            return $stmt->execute();
        }

        if (updatePassword($conn, 'users', $email, $hashed_password)) {
            $updated = true;
        } elseif (updatePassword($conn, 'admins', $email, $hashed_password)) {
            $updated = true;
        } elseif (updatePassword($conn, 'employees', $email, $hashed_password)) {
            $updated = true;
        }

        if ($updated) {
            $stmt = $conn->prepare("
                DELETE FROM password_resets WHERE 
                user_id IN (
                    SELECT user_id FROM users WHERE LOWER(email) = LOWER(?)
                )
                OR user_id IN (
                    SELECT id FROM admins WHERE LOWER(email) = LOWER(?)
                )
                OR user_id IN (
                    SELECT employee_id FROM employees WHERE LOWER(email) = LOWER(?)
                )
            ");
            $stmt->bind_param("sss", $email, $email, $email);
            $stmt->execute();

            session_unset();
            session_destroy();

            header("Location: login.php?reset=success");
            exit();
        } else {
            $message = "Failed to update password. Please try again.";
        }
    } else {
        $message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Adore & Beauty - Reset Password</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="style.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
<style>
    .invalid { color: red; }
    .valid { color: green; text-decoration: line-through; }
</style>
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
      <a href="login.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold hover:bg-gray-900 hover:text-white transition duration-300"> Login </a>
      <a href="sign-in.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold hover:bg-gray-900 hover:text-white transition duration-300"> Signup </a>
    </div>
  </div>
</header>

<section class="flex items-center justify-center min-h-screen mt-10 px-4">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Reset Password</h1>

        <?php if ($message): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="reset_password.php" id="resetForm" novalidate>
            <div class="mb-4">
                <label for="password" class="block font-semibold mb-1">New Password:</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    maxlength="30"
                    placeholder="Enter your new password"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div id="password-rules" class="mb-4 text-sm text-gray-600" style="display:none;">
                <p class="font-semibold mb-1">Password must have:</p>
                <ul class="list-disc list-inside">
                    <li id="rule-length" class="invalid">Minimum 6 characters</li>
                    <li id="rule-uppercase" class="invalid">At least 1 uppercase letter</li>
                    <li id="rule-lowercase" class="invalid">At least 1 lowercase letter</li>
                    <li id="rule-number" class="invalid">At least 1 number</li>
                    <li id="rule-special" class="invalid">At least 1 special character</li>
                </ul>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="block font-semibold mb-1">Confirm New Password:</label>
                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    maxlength="30"
                    placeholder="Confirm your new password"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    autocomplete="new-password"
                />
            </div>

            <button type="submit" class="w-full bg-teal-600 text-white p-2 rounded hover:bg-teal-700" id="submitBtn" disabled>
                Reset Password
            </button>
        </form>
    </div>
</section>

<script>
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');
    const passwordRules = document.getElementById('password-rules');
    const rules = {
        length: document.getElementById('rule-length'),
        uppercase: document.getElementById('rule-uppercase'),
        lowercase: document.getElementById('rule-lowercase'),
        number: document.getElementById('rule-number'),
        special: document.getElementById('rule-special')
    };

    function validateRules(val) {
        let validCount = 0;

        if (val.length >= 6) {
            rules.length.classList.add('valid');
            rules.length.classList.remove('invalid');
            validCount++;
        } else {
            rules.length.classList.remove('valid');
            rules.length.classList.add('invalid');
        }

        if (/[A-Z]/.test(val)) {
            rules.uppercase.classList.add('valid');
            rules.uppercase.classList.remove('invalid');
            validCount++;
        } else {
            rules.uppercase.classList.remove('valid');
            rules.uppercase.classList.add('invalid');
        }

        if (/[a-z]/.test(val)) {
            rules.lowercase.classList.add('valid');
            rules.lowercase.classList.remove('invalid');
            validCount++;
        } else {
            rules.lowercase.classList.remove('valid');
            rules.lowercase.classList.add('invalid');
        }

        if (/\d/.test(val)) {
            rules.number.classList.add('valid');
            rules.number.classList.remove('invalid');
            validCount++;
        } else {
            rules.number.classList.remove('valid');
            rules.number.classList.add('invalid');
        }

        if (/[\W_]/.test(val)) {
            rules.special.classList.add('valid');
            rules.special.classList.remove('invalid');
            validCount++;
        } else {
            rules.special.classList.remove('valid');
            rules.special.classList.add('invalid');
        }

        submitBtn.disabled = (validCount < 5 || val.length > 30);
    }

    passwordInput.addEventListener('input', (e) => {
        if(e.target.value.length > 0){
            passwordRules.style.display = 'block';
        } else {
            passwordRules.style.display = 'none';
        }
        validateRules(e.target.value);
    });
</script>

</body>
</html>
