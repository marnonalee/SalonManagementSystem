<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

include '../db.php';  

$admin_username = $_SESSION['admin_username'];
$admin_email = $_SESSION['admin_email'];
$error = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $old_password = $_POST['old_password'] ?? '';

    // Update username if changed
    if ($new_username !== $admin_username) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Username is already taken by another admin.";
        } else {
            $stmt = $conn->prepare("UPDATE admins SET username = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_username, $admin_username);
            if ($stmt->execute()) {
                $_SESSION['admin_username'] = $new_username;
                $admin_username = $new_username;
                $success_message = "Username updated successfully!";
            } else {
                $error = "Failed to update username.";
            }
            $stmt->close();
        }
    }

    // Update password if new password is provided
    if (!$error && !empty($new_password)) {
        if (empty($old_password)) {
            $error = "Please enter your old password to change it.";
        } else {
            // Get current hashed password
            $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
            $stmt->bind_param("s", $admin_username);
            $stmt->execute();
            $stmt->bind_result($current_hashed_password);
            $stmt->fetch();
            $stmt->close();

            // Verify old password
            if (!password_verify($old_password, $current_hashed_password)) {
                $error = "Old password is incorrect!";
            } else {
                if ($new_password !== $confirm_password) {
                    $error = "Passwords do not match!";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
                    $stmt->bind_param("ss", $hashed_password, $admin_username);
                    if ($stmt->execute()) {
                        $success_message .= ($success_message ? " " : "") . "Password updated successfully!";
                    } else {
                        $error = "Failed to update password.";
                    }
                    $stmt->close();
                }
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
   
</head>
<body class="bg-white text-gray-900">
    <div class="flex min-h-screen">
    <aside class="w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col px-6 py-8 bg-white z-20">
        <div class="flex items-center space-x-2 mb-4">
            <img src="img1.png" alt="User avatar" class="w-8 h-8 rounded-full object-cover" />
            <span class="text-slate-700 font-semibold text-2xl tracking-wide top-0">Welcome <?php echo htmlspecialchars($admin_username); ?></span>
        </div>

        <nav class="flex flex-col text-base space-y-2">
            <a class="sidebar-link" href="dashboard.php"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a class="sidebar-link" href="appointments.php"><i class="fas fa-calendar-alt"></i><span>Appointments</span></a>
            <a class="sidebar-link" href="employees.php"><i class="fas fa-user-tie"></i><span>Employees</span></a>
            <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
            <a class="sidebar-link " href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
            <a class="sidebar-link" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
            <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
            <a class="sidebar-link" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
            <a class="sidebar-link" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i> Calendar Settings</a>
            <a class="sidebar-link" href="terms_and_agreement.php"><i class="fas fa-users-cog"></i><span>Terms & Condition</span></a>
            <a class="sidebar-link" href="service_archive.php"><i class="fas fa-archive"></i><span>Archived</span></a>
        </nav>
        <div class="flex-grow"></div> 
        <div class="border-t border-gray-300 pt-4 flex flex-col space-y-2">
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="profile.php"><i class="fas fa-user-circle"></i><span>Profile</span></a>
            <a class="sidebar-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
        </aside>

        <main class="flex-1 p-8 ml-64 mt-16">
            <header class="fixed top-0 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
                <h1 class="text-sm font-semibold text-gray-900"> <i class="fas fa-th-large mr-2 text-gray-700 text-[14px]"></i>Profile Setting</h1>
            </header>

            <section>

                <?php if ($error): ?>
                    <div class="text-red-500 mb-4"><?php echo $error; ?></div>
                <?php elseif ($success_message): ?>
                    <div class="text-gray-500 mb-4"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form action="profile.php" method="POST" id="profileForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($admin_username); ?>" required
                            class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none text-sm">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin_email); ?>" readonly
                            class="w-full px-3 py-2 bg-gray-200 rounded-md border border-gray-300 text-gray-600 text-sm cursor-not-allowed">
                    </div>
                    <div class="border-t pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Change Password <span class="text-xs text-gray-500">(optional)</span></h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Old Password</label>
                                <input type="password" name="old_password" id="old_password" maxlength="50"
                                    class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none text-sm"
                                    autocomplete="current-password"
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password" id="new_password" maxlength="50"
                                    class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none text-sm"
                                    autocomplete="new-password"
                                >
                                <div id="passwordRules" class="mt-2 text-xs text-gray-600 bg-gray-50 border border-gray-300 rounded-md p-2" style="display: none;">
                                    <p>Password must contain:</p>
                                    <ul>
                                        <li id="lengthRule" class="rule invalid"><i class="icon fas fa-times"></i> At least 8 characters</li>
                                        <li id="uppercaseRule" class="rule invalid"><i class="icon fas fa-times"></i> One uppercase letter</li>
                                        <li id="lowercaseRule" class="rule invalid"><i class="icon fas fa-times"></i> One lowercase letter</li>
                                        <li id="numberRule" class="rule invalid"><i class="icon fas fa-times"></i> One number</li>
                                        <li id="specialRule" class="rule invalid"><i class="icon fas fa-times"></i> One special character (e.g., !@#$%)</li>
                                        <li id="maxLengthRule" class="rule valid"><i class="icon fas fa-check"></i> Maximum 50 characters</li>
                                    </ul>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" maxlength="50"
                                    class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none text-sm"
                                    autocomplete="new-password"
                                >
                                <p id="matchMessage" class="text-xs mt-1"></p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" id="submitBtn"
                                class="w-full sm:w-auto bg-slate-500 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-semibold transition duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordRules = document.getElementById('passwordRules');
        const submitBtn = document.getElementById('submitBtn');
        const matchMessage = document.getElementById('matchMessage');

        const lengthRule = document.getElementById('lengthRule');
        const uppercaseRule = document.getElementById('uppercaseRule');
        const lowercaseRule = document.getElementById('lowercaseRule');
        const numberRule = document.getElementById('numberRule');
        const specialRule = document.getElementById('specialRule');
        const maxLengthRule = document.getElementById('maxLengthRule');

        function validatePassword(password) {
            passwordRules.style.display = password.length > 0 ? 'block' : 'none';

            const lengthValid = password.length >= 8;
            const maxLengthValid = password.length <= 50;
            const uppercaseValid = /[A-Z]/.test(password);
            const lowercaseValid = /[a-z]/.test(password);
            const numberValid = /\d/.test(password);
            const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            updateRule(lengthRule, lengthValid);
            updateRule(maxLengthRule, maxLengthValid);
            updateRule(uppercaseRule, uppercaseValid);
            updateRule(lowercaseRule, lowercaseValid);
            updateRule(numberRule, numberValid);
            updateRule(specialRule, specialValid);

            return lengthValid && maxLengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid;
        }

        function updateRule(element, valid) {
            const icon = element.querySelector('.icon');
            if (valid) {
                element.classList.remove('invalid');
                element.classList.add('valid');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-check');
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                icon.classList.remove('fa-check');
                icon.classList.add('fa-times');
            }
        }

        function validateMatch() {
            if (confirmPasswordInput.value.length === 0) {
                matchMessage.textContent = '';
                return false;
            }
            if (newPasswordInput.value === confirmPasswordInput.value) {
                matchMessage.textContent = "Passwords match";
                matchMessage.style.color = "green";
                return true;
            } else {
                matchMessage.textContent = "Passwords do not match";
                matchMessage.style.color = "red";
                return false;
            }
        }

        function validateForm() {
            const password = newPasswordInput.value;
            const isPasswordValid = validatePassword(password);
            const isMatch = validateMatch();

            if (password.length === 0) {
                submitBtn.disabled = false;
                return true;
            }

            submitBtn.disabled = !(isPasswordValid && isMatch);
            return isPasswordValid && isMatch;
        }

        newPasswordInput.addEventListener('input', () => {
            validatePassword(newPasswordInput.value);
            validateMatch();
            validateForm();
        });

        confirmPasswordInput.addEventListener('input', () => {
            validateMatch();
            validateForm();
        });

        document.getElementById('profileForm').addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault();
            }
        });

        submitBtn.disabled = false;
    </script>
</body>
</html>
