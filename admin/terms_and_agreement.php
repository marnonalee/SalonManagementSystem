<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../db.php';

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

$query = "SELECT * FROM terms_conditions LIMIT 1";
$result = mysqli_query($conn, $query);
$terms = mysqli_fetch_assoc($result);

if (isset($_POST['save_terms'])) {
    $updated_content = mysqli_real_escape_string($conn, $_POST['terms_content']);
    $update_query = "UPDATE terms_conditions SET content = '$updated_content' WHERE id = {$terms['id']}";
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Terms and Conditions updated successfully.";
        $result = mysqli_query($conn, $query);
        $terms = mysqli_fetch_assoc($result);
    } else {
        $error_message = "Failed to update terms. Please try again.";
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
  <link rel="stylesheet" href="styles.css" />
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
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
    <a class="sidebar-link" href="user_management.php"><i class="fas fa-users-cog"></i><span  class="whitespace-nowrap">Users Management</span></a>
    <a class="sidebar-link" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
    <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
    <a class="sidebar-link" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
    <a class="sidebar-link" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i> Calendar Settings</a>
    <a class="sidebar-link  flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="terms_and_agreement.php"><i class="fas fa-users-cog"></i><span  class="whitespace-nowrap">Terms & Condition</span></a>
    <a class="sidebar-link" href="service_archive.php"><i class="fas fa-archive"></i><span>Archived</span></a>
  </nav>
  <div class="flex-grow"></div> 
  <div class="border-t border-gray-300 pt-4 flex flex-col space-y-2">
    <a class="sidebar-link" href="profile.php"><i class="fas fa-user-circle"></i><span>Profile</span></a>
    <a class="sidebar-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
  </div>
</aside>

  <main class="flex-1 p-8 ml-64 mt-16">
    <header class="fixed top-0 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
      <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-users-cog"></i> Terms and Condition</h1>
    </header>

    <div class="max-w-4xl mx-auto mt-8 bg-white border border-gray-200 rounded-lg shadow p-6">
      <?php if (isset($success_message)): ?>
        <p class="text-gray-600 mb-4"><?php echo $success_message; ?></p>
      <?php elseif (isset($error_message)): ?>
        <p class="text-red-600 mb-4"><?php echo $error_message; ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label class="block text-gray-700 font-semibold mb-2" for="terms_content">Edit Terms and Conditions:</label>
        <textarea name="terms_content" id="terms_content" rows="15" class="w-full border border-gray-300 rounded-md p-4 text-sm"><?php echo htmlspecialchars($terms['content']); ?></textarea>
        <button type="submit" name="save_terms" class="mt-4 bg-slate-500 hover:bg-slate-600 text-white px-6 py-2 rounded-md font-semibold">
          Save Changes
        </button>
      </form>
    </div>
  </main>
</div>


</body>
</html>
