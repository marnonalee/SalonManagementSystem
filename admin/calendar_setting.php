<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$show_modal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $open_days = $_POST['days'] ?? [];
    $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    foreach ($days_of_week as $day) {
        $is_open = in_array($day, $open_days) ? 1 : 0;
        $stmt = $conn->prepare("UPDATE calendar_settings SET is_open = ? WHERE day = ?");
        $stmt->bind_param("is", $is_open, $day);
        $stmt->execute();
    }

    $show_modal = true; 
}

$result = $conn->query("SELECT * FROM calendar_settings");
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['day']] = $row['is_open'];
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-gray-100">

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
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i><span> Calendar Settings</span></a>
            <a class="sidebar-link" href="terms_and_agreement.php"><i class="fas fa-users-cog"></i><span>Terms & Condition</span></a>
            <a class="sidebar-link" href="service_archive.php"><i class="fas fa-archive"></i><span>Archived</span></a>
        </nav>
        <div class="flex-grow"></div>
        <div class="border-t border-gray-300 pt-4 flex flex-col space-y-2">
            <a class="sidebar-link " href="profile.php"><i class="fas fa-user-circle"></i><span>Profile</span></a>
            <a class="sidebar-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
        </aside>

  <main class="flex-1 p-8 ml-64 mt-16">
    <header class="fixed top-0 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
      <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-calendar-alt"></i> Set Available Days</h1>
    </header>

    <div class="flex flex-col lg:flex-row justify-between items-start gap-8 mt-6">
      <form method="POST" class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
        <p class="mb-4 text-gray-600">Check the days the salon is open:</p>

        <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
          <div class="flex items-center mb-2">
            <input type="checkbox" id="<?= $day ?>" name="days[]" value="<?= $day ?>"
              <?= isset($settings[$day]) && $settings[$day] ? 'checked' : '' ?>
              class="mr-2 w-5 h-5 text-slate-500 border-gray-300 rounded focus:ring-slate-400">
            <label for="<?= $day ?>" class="text-gray-800"><?= $day ?></label>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="mt-4 bg-slate-500 text-white px-4 py-2 rounded hover:bg-slate-600">
          Save Settings
        </button>
      </form>

      <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/3 self-start">
        <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center justify-between">
          <button onclick="changeMonth(-1)" class="text-gray-600 hover:text-slate-500"><i class="fas fa-chevron-left"></i></button>
          <span id="monthYear" class="font-bold text-slate-600"></span>
          <button onclick="changeMonth(1)" class="text-gray-600 hover:text-slate-500"><i class="fas fa-chevron-right"></i></button>
        </h2>
        <div id="calendar" class="grid grid-cols-7 gap-2 text-center text-gray-800"></div>
      </div>
    </div>
  </main>
</div>

<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Settings Saved!</h2>
    <p class="text-gray-600 mb-6">Your calendar settings have been successfully updated.</p>
    <button onclick="closeModal()" class="bg-slate-500 hover:bg-slate-600 text-white px-4 py-2 rounded">
      OK
    </button>
  </div>
</div>

<script>
  function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
  }

  <?php if ($show_modal): ?>
    window.onload = function () {
      document.getElementById('successModal').classList.remove('hidden');
    };
  <?php endif; ?>

  let currentDate = new Date();

  function renderCalendar() {
    const calendar = document.getElementById("calendar");
    const monthYear = document.getElementById("monthYear");
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const today = new Date();

    const monthStart = new Date(year, month, 1).getDay();
    const monthEnd = new Date(year, month + 1, 0).getDate();

    const monthNames = ["January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"];
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    monthYear.textContent = `${monthNames[month]} ${year}`;
    calendar.innerHTML = '';

    weekdays.forEach(day => {
      const div = document.createElement("div");
      div.className = "font-semibold text-slate-600";
      div.textContent = day;
      calendar.appendChild(div);
    });

    for (let i = 0; i < monthStart; i++) {
      const blank = document.createElement("div");
      calendar.appendChild(blank);
    }

    for (let day = 1; day <= monthEnd; day++) {
      const div = document.createElement("div");
      div.textContent = day;

      if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
        div.className = "p-2 rounded bg-slate-500 text-white font-bold";
      } else {
        div.className = "p-2 rounded hover:bg-slate-100 cursor-pointer";
      }

      calendar.appendChild(div);
    }
  }

  function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset);
    renderCalendar();
  }

  renderCalendar();
</script>

</body>
</html>
