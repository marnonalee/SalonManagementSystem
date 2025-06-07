<?php 
session_start();
require '../db.php';

if (!isset($_SESSION['employee'])) {
    header("Location: ../login.php");
    exit();
}

$employee_name = $_SESSION['employee'];

$stmt = $conn->prepare("SELECT name, email, password, profile_image FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$employee_username = $row['name'];
$employee_email = $row['email'];
$employee_password_hash = $row['password'];
$profile_image = $row['profile_image'] ?? 'default.png';

$showSuccessModal = false;
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $fieldsToUpdate = [];
    $params = [];
    $paramTypes = '';

    $newProfileImageName = $profile_image; 
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = uniqid() . "." . $fileExt;
            $destination = "uploads/" . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $newProfileImageName = $newFileName;
                $fieldsToUpdate[] = "profile_image = ?";
                $params[] = $newProfileImageName;
                $paramTypes .= 's';
            } else {
                $errorMessage = "Failed to upload image.";
            }
        } else {
            $errorMessage = "Invalid image format.";
        }
    }

    if ($errorMessage === "" && $new_username !== $employee_username) {
        $fieldsToUpdate[] = "name = ?";
        $params[] = $new_username;
        $paramTypes .= 's';
    }

    if ($errorMessage === "" && !empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errorMessage = "Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
            $errorMessage = "Password must meet all requirements.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $fieldsToUpdate[] = "password = ?";
            $params[] = $hashed_password;
            $paramTypes .= 's';
        }
    }

    if ($errorMessage === "" && count($fieldsToUpdate) > 0) {
        $sql = "UPDATE employees SET " . implode(", ", $fieldsToUpdate) . " WHERE name = ?";
        $params[] = $employee_name;  
        $paramTypes .= 's';

        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param($paramTypes, ...$params);

        if ($update_stmt->execute()) {
            if (in_array("name = ?", $fieldsToUpdate)) {
                $_SESSION['employee'] = $new_username;
                $employee_name = $new_username;
                $employee_username = $new_username;
            }
            if (in_array("profile_image = ?", $fieldsToUpdate)) {
                $profile_image = $newProfileImageName;
            }
            $showSuccessModal = true;
        } else {
            $errorMessage = "Error updating profile.";
        }
    } elseif ($errorMessage === "") {
        $errorMessage = "No changes detected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-white text-gray-900">
  <div class="flex min-h-screen">
    <aside class="w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col px-6 py-8 bg-white z-20">
      <div class="flex items-center space-x-3 mb-10">
        <img src="uploads/<?= htmlspecialchars($profile_image); ?>" alt="User avatar" class="w-10 h-10 rounded-full object-cover" />
        <span class="text-slate-500 font-semibold text-lg">Welcome, <?= htmlspecialchars($employee_username); ?></span>
      </div>
      <nav class="flex flex-col text-base space-y-2">
        <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="dashboard_emp.php"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
        <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="my_appointments.php"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
        <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="messages.php"><i class="fas fa-comment-dots"></i><span>Messages</span></a>
        <a class="flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-100 text-slate-600" href="profile_emp.php"><i class="fas fa-user"></i><span>My Profile</span></a>
        <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
      </nav>
    </aside>

    <main class="flex-1 ml-64 p-6 pt-24">
      <header class="fixed top-0 mb-6 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
        <h1 class="text-sm font-semibold text-gray-900">Profile Setting</h1>
      </header>

      <section>
        <?php if (!empty($errorMessage)): ?>
          <div class="bg-red-100 text-red-600 px-4 py-2 rounded mb-4"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if ($showSuccessModal): ?>
          <div class="bg-green-100 text-green-600 px-4 py-2 rounded mb-4">Profile updated successfully!</div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="flex flex-col items-center mb-4">
            <img src="uploads/<?= htmlspecialchars($profile_image); ?>" alt="Profile Image"
                 class="w-24 h-24 rounded-full object-cover border border-gray-300" />
          </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Username</label>
              <input type="text" maxlength="50" id="username" name="username"
                value="<?= htmlspecialchars($employee_username); ?>" required
                class="w-full px-3 py-2 border rounded-md border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none" />
              <p class="text-sm text-gray-500 mt-1"><span id="usernameCount">0</span>/50</p>
            </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email"   maxlength="100" value="<?= htmlspecialchars($employee_email); ?>" readonly
              class="w-full px-3 py-2 bg-gray-200 border rounded-md border-gray-300 text-gray-600 cursor-not-allowed" />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" maxlength="100" id="new_password" name="new_password"
              class="w-full px-3 py-2 border rounded-md border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none" />
            <p class="text-sm text-gray-500 mt-1"><span id="passwordCount">0</span>/100</p>
            <ul id="passwordRequirements" class="text-sm text-gray-600 space-y-1 mt-2 hidden">
              <li id="length">• At least 8 characters</li>
              <li id="uppercase">• At least one uppercase letter (A-Z)</li>
              <li id="lowercase">• At least one lowercase letter (a-z)</li>
              <li id="number">• At least one number (0-9)</li>
              <li id="special">• At least one special character (@$!%*?&)</li>
            </ul>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" maxlength="100" id="confirm_password" name="confirm_password"
              class="w-full px-3 py-2 border rounded-md border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none" />
            <p class="text-sm text-gray-500 mt-1"><span id="confirmPasswordCount">0</span>/100</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Profile Image</label>
            <input type="file" name="profile_image" accept="image/*"
              class="w-full px-3 py-2 border rounded-md border-gray-300 focus:ring-2 focus:ring-slate-400 focus:outline-none" />
          </div>

          <button type="submit" name="submit"
            class="w-full bg-slate-500 text-white py-3 rounded-md hover:bg-slate-600 transition duration-200 font-semibold">
            Update Profile
          </button>
        </form>
      </section>
    </main>
  </div>

  <?php if ($showSuccessModal): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById('successModal').classList.remove('hidden');
    });
  </script>
  <?php endif; ?>
  <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-30 <?= $showSuccessModal ? '' : 'hidden' ?>">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
      <div class="flex justify-between items-center">
        <h2 class="text-green-600 text-lg font-semibold">Success</h2>
        <button onclick="document.getElementById('successModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>
      <p class="mt-4">Profile updated successfully!</p>
      <button onclick="document.getElementById('successModal').classList.add('hidden')" class="mt-6 bg-slate-500 text-white px-4 py-2 rounded hover:bg-slate-600">Close</button>
    </div>
  </div>

<div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-40 hidden">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
    <div class="flex justify-between items-center">
      <h2 class="text-red-600 text-lg font-semibold">Error</h2>
      <button onclick="document.getElementById('errorModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    <p class="mt-4" id="errorMessageContent">Invalid file type.</p>
    <button onclick="document.getElementById('errorModal').classList.add('hidden')" class="mt-6 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Close</button>
  </div>
</div>

  <script>

function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
  }

  const passwordInput = document.getElementById('new_password');
  const requirements = document.getElementById('passwordRequirements');

  const length = document.getElementById('length');
  const uppercase = document.getElementById('uppercase');
  const lowercase = document.getElementById('lowercase');
  const number = document.getElementById('number');
  const special = document.getElementById('special');

  passwordInput.addEventListener('input', function () {
    const value = passwordInput.value;
    requirements.classList.toggle('hidden', value.length === 0);

    length.classList.toggle('text-green-500', value.length >= 8);
    uppercase.classList.toggle('text-green-500', /[A-Z]/.test(value));
    lowercase.classList.toggle('text-green-500', /[a-z]/.test(value));
    number.classList.toggle('text-green-500', /\d/.test(value));
    special.classList.toggle('text-green-500', /[@$!%*?&]/.test(value));
  });
    const newPasswordInput = document.getElementById('new_password');
    const reqList = document.getElementById('passwordRequirements');

    newPasswordInput.addEventListener('focus', () => reqList.classList.remove('hidden'));
    newPasswordInput.addEventListener('blur', () => reqList.classList.add('hidden'));


    document.addEventListener('DOMContentLoaded', function () {
  const usernameInput = document.getElementById('username');
  const usernameCount = document.getElementById('usernameCount');

  const newPasswordInput = document.getElementById('new_password');
  const passwordCount = document.getElementById('passwordCount');

  const confirmPasswordInput = document.getElementById('confirm_password');
  const confirmPasswordCount = document.getElementById('confirmPasswordCount');

  function updateCount(input, counter) {
    if (input && counter) {
      counter.textContent = input.value.length;
    }
  }

  if (usernameInput) {
    usernameInput.addEventListener('input', () => updateCount(usernameInput, usernameCount));
    updateCount(usernameInput, usernameCount);
  }

  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', () => updateCount(newPasswordInput, passwordCount));
    updateCount(newPasswordInput, passwordCount);
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', () => updateCount(confirmPasswordInput, confirmPasswordCount));
    updateCount(confirmPasswordInput, confirmPasswordCount);
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const fileInput = form.querySelector("input[type='file']");
  const allowedExtensions = ["jpg", "jpeg", "png", "gif"];
  const errorModal = document.getElementById("errorModal");
  const errorMessageContent = document.getElementById("errorMessageContent");

  form.addEventListener("submit", function (e) {
    const file = fileInput.files[0];
    if (file) {
      const fileExtension = file.name.split('.').pop().toLowerCase();
      if (!allowedExtensions.includes(fileExtension)) {
        e.preventDefault();
        errorMessageContent.textContent = "Invalid file type. Please upload an image (JPG, PNG, GIF).";
        errorModal.classList.remove("hidden");
      }
    }
  });
});

  </script>
</body>
</html>
