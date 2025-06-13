<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];
$email = $_SESSION['email'];
$phone = $_SESSION['phone'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Adore & Beauty - Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap"/>
    <link rel="stylesheet" href="css/user_profiles.css"/>
    <script src="js/profile.js" defer></script>
</head>
<body class="bg-neutral-100">

<aside class="bg-[#1d3239] w-64 min-h-screen shadow-lg fixed top-0 left-0 px-6 py-10 space-y-6 hidden md:block z-50 text-[#f3f4f6]">
    <div class="absolute top-0 right-0 h-full w-8 bg-[#ffb199] rounded-l-full z-0"></div>
    <div class="relative z-10">
      <div class="flex items-center space-x-2">
        <img src="../images/logo1.png" alt="Adore & Beauty Logo" class="w-8" />
        <p class="text-white text-sm">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($username); ?></span>!</p>
      </div>
    </div>
    <nav class="flex flex-col space-y-4">
      <a href="services.php" class="hover:text-[#fe7762] flex items-center transition" ><i class="fas fa-cut mr-3"></i>Services</a>
      <a href="your_bookings.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-calendar-alt mr-3"></i>My Bookings</a>
      <a href="messages_page.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-user mr-3"></i>Messages</a>
      <div class="border-t border-[#334155] pt-4 mt-4">
        <a href="profile.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition"><i class="fas fa-user mr-3"></i>Profile</a>
        <a href="logout.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a>
      </div>
    </nav>
  </aside>

<main class="mt-24 mb-10 md:ml-80 px-4">
  <div class="max-w-4xl mx-auto bg-white/70 backdrop-blur-md rounded-3xl shadow-xl p-8">
    <h2 class="text-4xl font-bold text-gray-600 mb-8 text-center font-[Poppins]">User Settings</h2>

    <div class="flex flex-col md:flex-row gap-10">
      
      <aside class="md:w-1/3 flex flex-col items-center space-y-4">
        <div class="w-24 h-24 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-bold text-3xl shadow">
          <?= strtoupper(substr($username, 0, 1)); ?>
        </div>
        <button id="profile-button" class="tab-btn w-full text-left flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded transition">
          <i class="fas fa-user"></i> Profile Setting
        </button>

        <button id="password-button" class="tab-btn w-full text-left flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded transition">
          <i class="fas fa-lock"></i> Password
        </button>

      </aside>

      <section class="md:w-2/3 space-y-6">
        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
          <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">Profile updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['password_update']) && $_GET['password_update'] === 'success'): ?>
          <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">Password updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['password_update']) && $_GET['password_update'] === 'fail'): ?>
          <div id="error-message" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
            <?= htmlspecialchars($_GET['msg']); ?>
          </div>
        <?php endif; ?>

        <form id="profile-form" method="POST" action="php/update_profile.php" class="space-y-4">
          <div>
            <label for="username" class="block text-gray-700 font-semibold mb-2">Username</label>
            <input id="username" name="username" type="text" value="<?= htmlspecialchars($username); ?>" required
              maxlength="50"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div>
              <label for="phone" class="block text-gray-700 font-semibold mb-2">Phone Number</label>
              <input 
                id="phone" 
                name="phone" 
                type="tel" 
                value="<?= htmlspecialchars($phone); ?>" 
                required 
                maxlength="11"
                pattern="[0-9]{1,11}" 
                inputmode="numeric" 
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
          <div>
            <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
            <input id="email" type="email" value="<?= htmlspecialchars($email); ?>" disabled
              class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-100 text-gray-500">
            <p class="text-xs text-gray-400 mt-1">Email change is disabled</p>
          </div>

          <div class="text-right">
            <button type="submit" class="bg-[#1d3239] hover:bg-[#121f28] text-white px-6 py-2 rounded-xl font-semibold shadow-md transition">Update Account</button>
          </div>
        </form>

        <form id="password-form" method="POST" action="php/update_password.php" class="hidden space-y-4" onsubmit="return validatePasswordForm()">
          <div>
            <label for="old-password" class="block text-gray-700 font-semibold mb-2">Old Password</label>
            <input id="old-password" name="old-password" type="password" required  maxlength="100"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200">
          </div>

          <div>
            <label for="new-password" class="block text-gray-700 font-semibold mb-2">New Password</label>
            <input id="new-password" name="new-password" type="password" required  maxlength="100"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
              oninput="showRules(); checkPasswordStrength();">
            
            <ul id="password-rules" class="hidden mt-2 text-sm space-y-1 text-gray-600">
              <li id="length-rule" class="flex items-center gap-2"><i class="fas fa-circle text-xs"></i> At least 8 characters</li>
              <li id="uppercase-rule" class="flex items-center gap-2"><i class="fas fa-circle text-xs"></i> One uppercase letter</li>
              <li id="lowercase-rule" class="flex items-center gap-2"><i class="fas fa-circle text-xs"></i> One lowercase letter</li>
              <li id="number-rule" class="flex items-center gap-2"><i class="fas fa-circle text-xs"></i> One number</li>
              <li id="special-rule" class="flex items-center gap-2"><i class="fas fa-circle text-xs"></i> One special character</li>
            </ul>
          </div>

          <div>
            <label for="confirm-password" class="block text-gray-700 font-semibold mb-2">Confirm New Password</label>
            <input id="confirm-password" name="confirm-password" type="password" required  maxlength="100"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200">
          </div>

          <div class="text-right">
            <button type="submit" class="bg-[#1d3239] hover:bg-[#121f28]  text-white px-6 py-2 rounded-xl font-semibold shadow-md transition">Update Password</button>
          </div>
        </form>

      </section>
    </div>
  </div>
</main>
<script src="js/profile.js" defer></script>
</body>
</html>
