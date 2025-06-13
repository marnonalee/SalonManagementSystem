<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
require_once '../db.php';

$status_filter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;

$status_sql = $status_filter === 'all' ? '' : "WHERE a.appointment_status = ?";
$total_sql = "SELECT COUNT(*) as total FROM appointments a $status_sql";
$stmt = $conn->prepare($total_sql);
if ($status_sql) {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_appointments = $total_row['total'];
$total_pages = ceil($total_appointments / $rows_per_page);

$sql = "SELECT 
            a.appointment_id,
            u.username AS user_name,
            e.name AS employee_name,
            s.service_name,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.price,
            a.appointment_fee,
            a.appointment_status
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.user_id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        LEFT JOIN services s ON a.service_id = s.service_id
        $status_sql
        ORDER BY a.appointment_date DESC, a.start_time DESC
        LIMIT $rows_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
if ($status_sql) {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Appointments - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
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
        <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="appointments.php"><i class="fas fa-calendar-alt"></i><span>Appointments</span></a>
        <a class="sidebar-link" href="employees.php"><i class="fas fa-user-tie"></i><span>Employees</span></a>
        <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
        <a class="sidebar-link" href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
        <a class="sidebar-link" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
        <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
        <a class="sidebar-link" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
        <a class="sidebar-link" href="calendar_setting.php"><i class="fas fa-calendar-alt"></i><span>Calendar Settings</span></a>
        <a class="sidebar-link" href="terms_and_agreement.php"><i class="fas fa-users-cog"></i><span>Terms & Condition</span></a>
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
        <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-calendar-alt"></i> Appointment List</h1>
        <input type="text" id="searchInput" placeholder="Search..." class="w-80 px-4 py-2 border rounded-md focus:outline-none" />
      </header>

      <section class="mt-6">
        <div class="inline-flex space-x-2 px-2 py-2">
          <?php
            $tabs = ['all' => 'All', 'Pending' => 'Pending', 'Accepted' => 'Accepted', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'];
            foreach ($tabs as $key => $label):
          ?>
            <a href="?status=<?php echo $key; ?>" class="px-3 py-1  text-sm font-medium <?php echo $status_filter === $key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'; ?>">
              <?php echo $label; ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="bg-white shadow rounded-lg overflow-visible mt-4">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-2 py-2 text-center">ID</th>
                <th class="px-2 py-2 text-center">User</th>
                <th class="px-2 py-2 text-center">Service</th>
                <th class="px-2 py-2 text-center">Date</th>
                <th class="px-2 py-2 text-center">Time</th>
                <th class="px-2 py-2 text-center">Employee</th>
                <th class="px-2 py-2 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php $serial = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td class="px-2 py-2 text-center"><?php echo $serial++; ?></td>
                  <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($row['user_name']); ?></td>
                  <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($row['service_name']); ?></td>
                  <td class="px-2 py-2 text-center"><?php echo $row['appointment_date']; ?></td>
                  <td class="px-2 py-2 text-center"><?php echo date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time'])); ?></td>
                  <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($row['employee_name']); ?></td>
                  <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($row['appointment_status']); ?></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center px-4 py-4 text-gray-500">No appointments found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
          <p id="noResultsMessage" class="text-center text-gray-500 mt-4 hidden">No results found.</p>

          <div class="mt-4 px-3 py-1 border border-gray-300 rounded-md inline-flex space-x-1 text-xs font-medium select-none bg-gray-100">
            <?php if ($page > 1): ?>
              <a href="?status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>" class="px-2 py-1 rounded hover:bg-gray-300 bg-gray-200 text-gray-700">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <a href="?status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>" class="px-2 py-1 rounded <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
              <a href="?status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>" class="px-2 py-1 rounded hover:bg-gray-300 bg-gray-200 text-gray-700">Next</a>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    document.getElementById('searchInput').addEventListener('input', function () {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      let anyVisible = false;
      rows.forEach(row => {
        const rowText = row.innerText.toLowerCase();
        const match = rowText.includes(filter);
        row.style.display = match ? '' : 'none';
        if (match) anyVisible = true;
      });
      document.getElementById('noResultsMessage').classList.toggle('hidden', anyVisible);
    });
  </script>
</body>
</html>
