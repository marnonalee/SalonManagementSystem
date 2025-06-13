<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

require_once '../db.php';

$rows_per_page = 10;

$service_page = isset($_GET['service_page']) && is_numeric($_GET['service_page']) ? (int)$_GET['service_page'] : 1;
$service_offset = ($service_page - 1) * $rows_per_page;

$user_page = isset($_GET['user_page']) && is_numeric($_GET['user_page']) ? (int)$_GET['user_page'] : 1;
$user_offset = ($user_page - 1) * $rows_per_page;

$totalServicesResult = $conn->query("SELECT COUNT(*) AS total FROM services WHERE is_archived = 1");
$totalServicesRow = $totalServicesResult->fetch_assoc();
$total_services = $totalServicesRow['total'];
$total_service_pages = ceil($total_services / $rows_per_page);

$totalUsersResult = $conn->query("SELECT COUNT(*) AS total FROM users WHERE archived = 1");
$totalUsersRow = $totalUsersResult->fetch_assoc();
$total_users = $totalUsersRow['total'];
$total_user_pages = ceil($total_users / $rows_per_page);

$sql = "SELECT * FROM services WHERE is_archived = 1 ORDER BY created_at DESC LIMIT $service_offset, $rows_per_page";
$result = $conn->query($sql);

$userSql = "SELECT * FROM users WHERE archived = 1 ORDER BY archived_at DESC LIMIT $user_offset, $rows_per_page";
$userResult = $conn->query($userSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Archived Services & Users - Admin</title>
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
        <a class="sidebar-link" href="appointments.php"><i class="fas fa-calendar-alt"></i><span>Appointments</span></a>
        <a class="sidebar-link" href="employees.php"><i class="fas fa-user-tie"></i><span>Employees</span></a>
        <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
        <a class="sidebar-link" href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
        <a class="sidebar-link" href="payments.php"><i class="fas fa-receipt"></i><span>Payment Records</span></a>
        <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
        <a class="sidebar-link" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
        <a class="sidebar-link" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i> Calendar Settings</a>
        <a class="sidebar-link" href="terms_and_agreement.php"><i class="fas fa-users-cog"></i><span>Terms & Condition</span></a>
        <a class="sidebar-link  flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="service_archive.php"><i class="fas fa-archive"></i><span>Archived</span></a>
      </nav>
      <div class="flex-grow"></div>
      <div class="border-t border-gray-300 pt-4 flex flex-col space-y-2">
        <a class="sidebar-link" href="profile.php"><i class="fas fa-user-circle"></i><span>Profile</span></a>
        <a class="sidebar-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
      </div>
    </aside>

    <main class="flex-1 p-8 ml-64 mt-16">
      <header class="fixed top-0 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
        <h1 class="text-sm font-semibold text-gray-900">
          <i class="fas fa-archive mr-2 text-gray-700 text-[14px]"></i> Archived List
        </h1>
      </header>

      <section>
        <h2 class="text-lg font-semibold mb-4">Archived Services</h2>
        <div class="overflow-x-auto rounded-lg border border-gray-100">
          <table class="w-full text-xs text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-600 font-semibold">
              <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Service Name</th>
                <th class="px-4 py-3">Time</th>
                <th class="px-4 py-3">Price</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php 
              $counter = $service_offset + 1; 
              if ($result->num_rows > 0): 
                while ($row = $result->fetch_assoc()):
                  $minutes = (int)$row['duration'];
                  $hours = floor($minutes / 60);
                  $mins = $minutes % 60;
                  $formattedDuration = ($hours ? "{$hours} hr" . ($hours > 1 ? "s" : "") : "") . 
                                       ($hours && $mins ? " " : "") . 
                                       ($mins ? "{$mins} min" . ($mins > 1 ? "s" : "") : "");
              ?>
                <tr>
                  <td class="px-4 py-3"><?php echo $counter++; ?></td>
                  <td class="px-4 py-3"><?php echo htmlspecialchars($row['service_name']); ?></td>
                  <td class="px-4 py-3"><?php echo $formattedDuration; ?></td>
                  <td class="px-4 py-3">₱<?php echo number_format($row['price'], 2); ?></td>
                  <td class="px-4 py-3">
                    <span class="text-red-500 font-medium">Archived</span>
                  </td>
                  <td class="px-4 py-3 space-x-2">
                    <form method="POST" action="php/restore_service.php" class="inline" onsubmit="return confirm('Restore this service?');">
                      <input type="hidden" name="service_id" value="<?php echo (int)$row['service_id']; ?>">
                      <button type="submit" class="text-green-600 hover:text-green-800 text-xl" title="Restore Service">
                        <i class="fas fa-arrow-alt-circle-up"></i>
                      </button>
                    </form>

                    <form method="POST" action="php/delete_service.php" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this service?');">
                      <input type="hidden" name="service_id" value="<?php echo (int)$row['service_id']; ?>">
                      <button type="submit" class="text-red-600 hover:text-red-800 text-xl" title="Delete Permanently">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php 
                endwhile;
              else: 
              ?>
                <tr>
                  <td colspan="6" class="px-4 py-3 text-center text-gray-400">No archived services found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex justify-center space-x-2">
          <?php if ($total_service_pages > 1): ?>
            <?php for ($p = 1; $p <= $total_service_pages; $p++): ?>
              <a href="?service_page=<?php echo $p; ?>&user_page=<?php echo $user_page; ?>"
                 class="px-3 py-1 rounded <?php echo ($p === $service_page) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                <?php echo $p; ?>
              </a>
            <?php endfor; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="mt-12">
        <h2 class="text-lg font-semibold mb-4">Archived Users</h2>
        <div class="overflow-x-auto rounded-lg border border-gray-100">
          <table class="w-full text-xs text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-600 font-semibold">
              <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Username</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php 
              $userCounter = $user_offset + 1;
              if ($userResult->num_rows > 0): 
                while ($userRow = $userResult->fetch_assoc()):
              ?>
                <tr>
                  <td class="px-4 py-3"><?php echo $userCounter++; ?></td>
                  <td class="px-4 py-3"><?php echo htmlspecialchars($userRow['username']); ?></td>
                  <td class="px-4 py-3"><?php echo htmlspecialchars($userRow['email']); ?></td>
                  <td class="px-4 py-3">
                    <span class="text-red-500 font-medium">Archived</span>
                  </td>
                  <td class="px-4 py-3 space-x-2">
                    <form method="POST" action="php/restore_user.php" class="inline" onsubmit="return confirm('Restore this user?');">
                      <input type="hidden" name="user_id" value="<?php echo (int)$userRow['user_id']; ?>">
                      <button type="submit" class="text-green-600 hover:text-green-800 text-xl" title="Restore User">
                        <i class="fas fa-arrow-alt-circle-up"></i>
                      </button>
                    </form>

                    <form method="POST" action="php/delete_user.php" class="inline" onsubmit="return confirm('Permanently delete this user?');">
                      <input type="hidden" name="user_id" value="<?php echo (int)$userRow['user_id']; ?>">
                      <button type="submit" class="text-red-600 hover:text-red-800 text-xl" title="Delete Permanently">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php 
                endwhile; 
              else: 
              ?>
                <tr>
                  <td colspan="5" class="px-4 py-3 text-center text-gray-400">No archived users found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex justify-center space-x-2">
          <?php if ($total_user_pages > 1): ?>
            <?php for ($p = 1; $p <= $total_user_pages; $p++): ?>
              <a href="?user_page=<?php echo $p; ?>&service_page=<?php echo $service_page; ?>"
                 class="px-3 py-1 rounded <?php echo ($p === $user_page) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                <?php echo $p; ?>
              </a>
            <?php endfor; ?>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
