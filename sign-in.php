<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (strlen($username) > 50) {
        $error = "Username must not exceed 50 characters.";
    } elseif (strlen($email) > 50) {
        $error = "Email must not exceed 50 characters.";
    }

    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $special   = preg_match('@[^\w]@', $password);
    $length    = strlen($password) >= 8;

    if (empty($error)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username already exists!";
        }
        $stmt->close();
    }

    if (empty($error)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        }
        $stmt->close();
    }

    if (empty($error)) {
        if (!$length || !$uppercase || !$lowercase || !$number || !$special) {
            $error = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match!";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['signup_data'] = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'otp' => $otp
            ];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'salonaandb@gmail.com';
                $mail->Password = 'oqnblesgwkekaxcg';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('salonaandb@gmail.com', 'Adore & Beauty');
                $mail->addAddress($email, $username);
                $mail->Subject = 'Your OTP Code';
                $mail->Body    = "Your OTP code is: $otp";

                $mail->send();
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                $error = "OTP could not be sent. Mailer Error: " . $mail->ErrorInfo;
            }
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
    <title>Adore & Beauty - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <style>
        .error-input {
            border-color: red !important;
        }
    </style>
</head>
<body style="background-image: url('images/bg.jpg')">
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
      <a href="guide.php" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
        <i class="fas fa-book"></i> Beauty & Style Guide
      </a>
    </nav>

    <div class="flex space-x-4">
      <a href="login.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
        Login
      </a>
      <a href="sign-in.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-white font-semibold bg-gray-900 hover:bg-gray-700 transition duration-300 transform scale-105">
        Signup
      </a>
    </div>
  </div>
</header>

<section class="flex items-center justify-center min-h-screen mt-10">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-end">
            <a href="login.php" class="cursor-pointer">
                <i class="fas fa-times text-gray-500"></i>
            </a>
        </div>
        <h1 class="text-2xl font-bold text-center mb-2">Create your Account</h1>

        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="sign-in.php" method="POST" novalidate>
            <div class="mb-4">
                Username 
                <input
                    type="text"
                    name="username"
                    id="username"
                    placeholder="Username"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    minlength="3"
                    maxlength="50"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                />
            </div>

            <div class="mb-4">
                Email 
                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="Email"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    maxlength="50"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                />
            </div>

            <div class="mb-4">
                Password 
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Password"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    maxlength="100"
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                    title="Must be at least 8 characters long, include uppercase, lowercase, number, and special character."
                    autocomplete="new-password"
                />
                <div id="password-rules" class="text-sm text-gray-600 mt-2">
                    <p>Password must have:</p>
                    <ul class="list-disc ml-5">
                        <li id="rule-length" class="text-red-600">At least 8 characters</li>
                        <li id="rule-uppercase" class="text-red-600">At least one uppercase letter (A-Z)</li>
                        <li id="rule-lowercase" class="text-red-600">At least one lowercase letter (a-z)</li>
                        <li id="rule-number" class="text-red-600">At least one number (0-9)</li>
                        <li id="rule-special" class="text-red-600">At least one special character (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            <div class="mb-6">
                Confirm Password
                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    placeholder="Confirm Password"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    autocomplete="new-password"
                />
            </div>
            <button
                type="submit"
                class="w-full bg-teal-600 text-white py-2 rounded-lg hover:bg-teal-700"
            >
                SIGN UP
            </button>
        </form>
    </div>
</section>

<script>
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordRules = document.getElementById('password-rules');

    function validate() {
        const val = passwordInput.value;
        passwordRules.style.display = val.length > 0 ? 'block' : 'none';

        document.getElementById('rule-length').style.color = val.length >= 8 ? 'green' : 'red';
        document.getElementById('rule-uppercase').style.color = /[A-Z]/.test(val) ? 'green' : 'red';
        document.getElementById('rule-lowercase').style.color = /[a-z]/.test(val) ? 'green' : 'red';
        document.getElementById('rule-number').style.color = /[0-9]/.test(val) ? 'green' : 'red';
        document.getElementById('rule-special').style.color = /[^\w]/.test(val) ? 'green' : 'red';
    }

    passwordInput.addEventListener('input', validate);
    validate();

    const usernameInput = document.getElementById('username');
    const usernameCount = document.getElementById('username-count');
    usernameInput.addEventListener('input', () => {
        const len = usernameInput.value.length;
        usernameCount.textContent = `${len}/50`;
    });

    const emailInput = document.getElementById('email');
    const emailCount = document.getElementById('email-count');
    emailInput.addEventListener('input', () => {
        const len = emailInput.value.length;
        emailCount.textContent = `${len}/50`;
    });

    const passwordCount = document.getElementById('password-count');
    passwordInput.addEventListener('input', () => {
        const len = passwordInput.value.length;
        passwordCount.textContent = `${len}/100`;
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        let isValid = true;
        let emptyFields = false;
        const fields = ['username', 'email', 'password', 'confirm_password'];

        fields.forEach(id => {
            const input = document.getElementById(id);
            if (!input.value.trim()) {
                input.classList.add('error-input');
                emptyFields = true;
                isValid = false;
            } else {
                input.classList.remove('error-input');
            }
        });

        const oldMsg = document.getElementById('empty-fields-msg');
        if (oldMsg) oldMsg.remove();

        if (emptyFields) {
            e.preventDefault(); 
            const form = document.querySelector('form');
            const errorDiv = document.createElement('div');
            errorDiv.id = 'empty-fields-msg';
            errorDiv.className = 'mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded';
            errorDiv.innerText = 'Please fill in all fields.';
            form.parentNode.insertBefore(errorDiv, form); 
        }
    });
</script>

</body>
</html>
