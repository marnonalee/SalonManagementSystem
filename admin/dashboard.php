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

$employee_result = $conn->query("SELECT COUNT(*) AS total_employees FROM employees");
$employee_data = $employee_result->fetch_assoc();
$total_employees = $employee_data['total_employees'];

$customer_result = $conn->query("SELECT COUNT(*) AS total_customers FROM users");
$customer_data = $customer_result->fetch_assoc();
$total_customers = $customer_data['total_customers'];

$completed_appointment_result = $conn->query("SELECT COUNT(*) AS total_bookings FROM appointments WHERE appointment_status = 'Completed'");
$completed_appointment_data = $completed_appointment_result->fetch_assoc();
$total_bookings = $completed_appointment_data['total_bookings'];

$current_year = date('Y');
$sql = "
    SELECT MONTH(appointment_date) AS month, COUNT(*) AS total 
    FROM appointments 
    WHERE appointment_status = 'Completed' 
      AND YEAR(appointment_date) = $current_year
    GROUP BY MONTH(appointment_date)
    ORDER BY MONTH(appointment_date)
";
$result = $conn->query($sql);
$monthly_bookings = array_fill(0, 12, 0);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthly_bookings[(int)$row['month'] - 1] = (int)$row['total'];
    }
}

$sql_weekly = "
    SELECT WEEK(appointment_date, 1) AS week_num, COUNT(*) AS total
    FROM appointments
    WHERE appointment_status = 'Pending'
      AND appointment_date >= CURDATE()
      AND appointment_date < CURDATE() + INTERVAL 4 WEEK
    GROUP BY WEEK(appointment_date, 1)
    ORDER BY week_num ASC
";
$result_weekly = $conn->query($sql_weekly);
$current_week = (int)date('W');
$weekly_bookings = array_fill(0, 4, 0);

if ($result_weekly) {
    while ($row = $result_weekly->fetch_assoc()) {
        $index = (int)$row['week_num'] - $current_week;
        if ($index >= 0 && $index < 4) {
            $weekly_bookings[$index] = (int)$row['total'];
        }
    }
}

$result = $conn->query("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()");
$row = $result->fetch_assoc();
$todaysAppointments = $row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-white text-gray-900">

  <div class="flex min-h-screen">
  <aside class="w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col px-6 py-8 bg-white z-20">
        <div class="flex items-center space-x-2 mb-4">
        <img src="img1.png" alt="User avatar" class="w-8 h-8 rounded-full object-cover" />
            <span class="text-slate-700 font-semibold text-2xl tracking-wide top-0">Welcome <?php echo htmlspecialchars($admin_username); ?></span>
        </div>

        <nav class="flex flex-col text-base space-y-2">
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="dashboard.php"><i class="fas fa-th-large"></i><span> Dashboard</span></a>
            <a class="sidebar-link" href="appointments.php"><i class="fas fa-calendar-alt"></i><span> Appointments</span></a>
            <a class="sidebar-link" href="employees.php"><i class="fas fa-user-tie"></i><span>Employees</span></a>
            <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
            <a class="sidebar-link " href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
            <a class="sidebar-link" href="payments.php"><i class="fas fa-receipt"></i><span>Payment Records</span></a>
            <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
            <a class="sidebar-link" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
            <a class="sidebar-link" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i> Calendar Settings</a>
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
           <h1 class="text-sm font-semibold text-gray-900">   <i class="fas fa-th-large mr-2 text-gray-700 text-[14px]"></i> Overview</h1>
          </header>

      <section>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
          <div class="bg-indigo-100 rounded-lg p-5 flex justify-between items-center">
            <div>
              <p class="text-xs text-gray-700 font-semibold mb-1">Total Bookings</p>
              <p class="text-lg font-bold text-gray-900"><?php echo $total_bookings; ?></p>

            </div>
            <button aria-label="Calendar icon" class="border border-indigo-300 rounded-md p-2 text-indigo-600 hover:bg-indigo-200">
              <i class="far fa-calendar-alt text-[18px]"></i>
            </button>
          </div>

          <div class="bg-pink-50 rounded-lg p-5 flex justify-between items-center">
          <div>
            <p class="text-xs text-gray-900 font-semibold mb-1">Today’s Appointments</p>
            <p class="text-lg font-bold text-gray-900"><?php echo $todaysAppointments; ?></p>
          </div>
            <button aria-label="Dollar icon" class="border border-pink-300 rounded-md p-2 text-pink-600 hover:bg-pink-200">
              <i class="fas fa-dollar-sign text-[18px]"></i>
            </button>
          </div>

          <div class="bg-indigo-50 rounded-lg p-5 flex justify-between items-center">
            <div>
              <p class="text-xs text-gray-700 font-semibold mb-1">Total Employees</p>
              <p class="text-lg font-bold text-gray-900"><?php echo $total_employees; ?></p>
            </div>
            <button aria-label="Users icon" class="border border-indigo-300 rounded-md p-2 text-indigo-600 hover:bg-indigo-200">
              <i class="fas fa-users text-[18px]"></i>
            </button>
          </div>

          <div class="bg-pink-50 rounded-lg p-5 flex justify-between items-center">
            <div>
              <p class="text-xs text-gray-900 font-semibold mb-1">Total User</p>
              <p class="text-lg font-bold text-gray-900"><?php echo $total_customers; ?></p>
            </div>
            <button aria-label="User group icon" class="border border-pink-300 rounded-md p-2 text-pink-600 hover:bg-pink-200">
              <i class="fas fa-user-friends text-[18px]"></i>
            </button>
          </div>
        </div>

      </section>

      <section class="mt-10">
        <h2 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-chart-line mr-2"></i> Booking Trends</h2>
        <div class="bg-white shadow-md rounded-lg p-6">
          <canvas id="bookingLineChart" height="100"></canvas>
        </div>
      </section>

    </main>

  </div>
  <script>
  window.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('bookingLineChart').getContext('2d');
    const monthlyBookings = <?php echo json_encode(array_values($monthly_bookings)); ?>;
    const weeklyBookings = <?php echo json_encode($weekly_bookings); ?>;

    if (window.bookingChart) {
      window.bookingChart.destroy();
    }

    window.bookingChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Completed Bookings',
          data: monthlyBookings,
          fill: false,
          borderColor: 'rgb(99, 102, 241)',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Monthly Completed Bookings (' + new Date().getFullYear() + ')'
          }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  });
</script>

</body>
</html>
