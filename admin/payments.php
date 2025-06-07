<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

require_once '../db.php';

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;

$total_sql = "SELECT COUNT(*) as total FROM appointments";
$total_result = $conn->query($total_sql);
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
            p.payment_status,
            p.created_at,           
            a.appointment_status,
            p.payment_proof
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.user_id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN payments p ON a.appointment_id = p.appointment_id
        ORDER BY a.appointment_date DESC, a.start_time DESC
        LIMIT $rows_per_page OFFSET $offset";

$result = $conn->query($sql);

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
  <style>
      body {
        font-family: 'Inter', sans-serif;
      }
      .payment-proof-img {
        max-width: 100px;
        max-height: 60px;
      }
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
            <a class="sidebar-link " href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
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
        <h1 class="text-sm font-semibold text-gray-900"><i class=" fas fa-dollar-sign"></i> User Payments </h1>
        <input type="text" id="searchInput" placeholder="Search..." class="w-80 px-4 py-2 border rounded-md focus:outline-none" />
         
      </header>

      <section >
      <div class="bg-white shadow rounded-lg overflow-visible">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-2 py-2 text-center  tracking-wider w-10 whitespace-nowrap">ID</th>
            <th class="px-2 py-2 text-center  tracking-wider max-w-[100px] truncate">User</th>
            <th class="px-2 py-2 text-center  tracking-wider max-w-[120px] truncate">Service</th>
            <th class="px-2 py-2 text-center tracking-wider whitespace-nowrap">Payment Date</th>
            <th class="px-2 py-2 text-center  tracking-wider whitespace-nowrap">Payment Status</th>
            <th class="px-2 py-2 text-center  tracking-wider w-28 whitespace-nowrap">Payment Proof</th>
            <th class="px-2 py-2 text-center  tracking-wider w-28 whitespace-nowrap">Actions</th>
          </tr>

          </thead>

          <tbody class="divide-y divide-gray-200 bg-white text-sm">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php $serial = 1; // Initialize counter 
                  while ($row = $result->fetch_assoc()): 
                ?>
                  <tr>
                      <td class="px-4 py-2 text-sm"><?php echo $serial++; ?></td>
                      <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></td>
                      <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($row['service_name'] ?? 'N/A'); ?></td>
                      <td class="px-4 py-2 text-center text-sm">
                        <?php 
                          if (!empty($row['created_at'])) {
                              echo date("M d, Y g:i A", strtotime($row['created_at']));
                          } else {
                              echo '<span class="italic text-gray-400">N/A</span>';
                          }
                        ?>
                    </td>
                      <td class="px-4 py-2 text-center text-sm"><?php echo htmlspecialchars($row['payment_status']); ?></td>
                      
                     <td class="px-4 py-2 text-sm text-center">
                        <?php if (!empty($row['payment_proof'])): ?>
                          <img 
                            src="../user/uploads/<?php echo htmlspecialchars($row['payment_proof']); ?>" 
                            class="payment-proof-img rounded cursor-pointer hover:scale-105 transition-transform"
                            onclick="openModal(this.src)" 
                            alt="Payment Proof"
                          />
                        <?php else: ?>
                          <span class="italic text-gray-400">No Proof</span>
                        <?php endif; ?>
                      </td>

                      <td class="px-4 py-2 text-sm text-center">
                          <?php
                              echo '<!-- Debug: ' . $row['payment_status'] . ' | Proof: ' . $row['payment_proof'] . ' -->';
                              if ($row['payment_status'] === 'Paid') {
                                  echo '<span class="text-green-600 font-semibold">✔ Paid</span>';
                              } elseif ($row['payment_status'] === 'Pending Verification') {
                                  if (!empty($row['payment_proof'])) {
                                      echo '<form method="POST" action="php/update_status.php">
                                              <input type="hidden" name="appointment_id" value="' . $row['appointment_id'] . '">
                                              <button type="submit" name="update_status" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">
                                                  Confirm Payment
                                              </button>
                                            </form>';
                                  } else {
                                      echo '<span class="italic text-yellow-500">Awaiting Proof</span>';
                                  }
                              } else {
                                  echo '<span class="italic text-gray-400">No Actions</span>';
                              }
                          ?>
                      </td>
                  </tr>
                  <?php endwhile; ?>
              <?php else: ?>
                  <tr>
                  <td colspan="10" class="px-4 py-4 text-center text-sm text-gray-500">No appointments found.</td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>

          <div class="pagination flex space-x-2 mt-4">
            <?php if ($page > 1): ?>
              <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 bg-slate-200 text-slate-900 rounded">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): 
              $active_class = ($i == $page) ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700';
            ?>
              <a href="?page=<?php echo $i; ?>" class="px-3 py-1 rounded <?php echo $active_class; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
              <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 bg-slate-200 text-slate-900 rounded">Next</a>
            <?php endif; ?>
            </div>

        </div>
      </section>
    </main>
  </div>

<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
  <div class="relative bg-white p-4 rounded-lg shadow-lg">
    <button onclick="closeModal()" class="absolute top-2 right-2 text-red-500 text-lg font-bold">&times;</button>
    <img id="modalImage" src="" alt="Payment Proof" class="max-w-full max-h-[80vh] rounded-lg" />
  </div>
</div>




  <script>

    document.getElementById('searchInput').addEventListener('input', function () {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(filter) ? '' : 'none';
      });
    });


    function openModal(imageSrc) {
      const modal = document.getElementById('imageModal');
      const modalImage = document.getElementById('modalImage');
      modalImage.src = imageSrc;
      modal.classList.remove('hidden');
    }

    function closeModal() {
      document.getElementById('imageModal').classList.add('hidden');
    }
  </script>

</body>
</html>
