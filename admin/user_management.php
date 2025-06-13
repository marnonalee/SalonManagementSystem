<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../db.php'; 

$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalSql = "SELECT COUNT(*) AS total FROM users";
$totalResult = mysqli_query($conn, $totalSql);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $limit);
$sql = "
    SELECT 
        u.user_id,
        u.username,
        u.phone,
        u.email,
        u.status,
        COUNT(a.appointment_id) AS total_appointments
    FROM users u
    LEFT JOIN appointments a ON u.user_id = a.user_id
    WHERE u.archived = 0
    GROUP BY u.user_id, u.username, u.phone, u.email, u.status
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Management - Admin</title>
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
    <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="user_management.php"><i class="fas fa-users-cog"></i><span  class="whitespace-nowrap">User Management</span></a>
    <a class="sidebar-link" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
    <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
    <a class="sidebar-link" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
    <a class="sidebar-link" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i> Calendar Settings</a>
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
      <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-users-cog mr-2 text-gray-700 text-[14px]"></i> User List</h1>
      <input type="text" id="searchInput" placeholder="Search..." class="w-80 px-4 py-2 border rounded-md focus:outline-none" />
    </header>

    <section>
      <div class="bg-white shadow rounded-lg overflow-visible">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">CUSTOMER NAME</th>
              <th class="px-4 py-3">PHONE</th>
              <th class="px-4 py-3">EMAIL</th>
              <th class="px-4 py-3">STATUS</th>
              <th class="px-4 py-3">TOTAL APPOINTMENT</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td class="px-4 py-3"><?php echo htmlspecialchars($row['user_id']); ?></td>
                <td class="px-4 py-3"><?php echo htmlspecialchars($row['username']); ?></td>
                <td class="px-4 py-3"><?php echo htmlspecialchars($row['phone']); ?></td>
                <td class="px-4 py-3"><?php echo htmlspecialchars($row['email']); ?></td>
                <td class="px-4 py-3">
                  <span class="px-3 py-1 rounded-full text-xs <?php 
                    echo $row['status'] === 'active' ? 'bg-green-200 text-gray-800' : (
                      $row['status'] === 'blocked' ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-gray-800'
                    );
                  ?>">
                    <?php echo ucfirst($row['status']); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center"><?php echo htmlspecialchars($row['total_appointments']); ?></td>
                <td class="px-4 py-3 border-b text-center relative">
                  <d iv class="relative inline-block text-left">
                    <button onclick="toggleDropdown(this)" 
                      class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                      Actions <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                    
                    <div class="dropdown-menu absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg hidden z-50">
                   
                      <button 
                        onclick="openEditModal(
                          <?php echo $row['user_id']; ?>, 
                          '<?php echo addslashes($row['username']); ?>', 
                          '<?php echo addslashes($row['phone']); ?>', 
                          '<?php echo addslashes($row['email']); ?>'
                        )" 
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-edit mr-1"></i> Edit
                      </button>

                      <?php
                        if ($row['status'] === 'inactive') {
                            $deactivate_action = 'activate';
                            $deactivate_text = 'Activate';
                            $deactivate_icon = 'fas fa-check-circle';  
                            $deactivate_class = 'text-green-600';
                        } else {
                            $deactivate_action = 'deactivate';
                            $deactivate_text = 'Deactivate';
                            $deactivate_icon = 'fas fa-ban';
                            $deactivate_class = 'text-yellow-600';
                        }
                        ?>

                        <a href="php/toggle_status.php?id=<?php echo $row['user_id']; ?>&action=<?php echo $deactivate_action; ?>" 
                          class="block px-4 py-2 text-sm <?php echo $deactivate_class; ?> hover:bg-gray-100">
                          <i class="<?php echo $deactivate_icon; ?> mr-1"></i> <?php echo $deactivate_text; ?>
                        </a>

                        <button 
                          onclick="openArchiveModal(<?php echo $row['user_id']; ?>)"
                          class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                          <i class="fas fa-archive mr-1"></i> Archive
                        </button>


                      <?php
                          $toggleAction = $row['status'] === 'blocked' ? 'unblock' : 'block';
                          $toggleLabel = $row['status'] === 'blocked' ? 'Unblock' : 'Block';
                          $toggleIcon = $row['status'] === 'blocked' ? 'fa-user-check' : 'fa-user-slash';
                          $toggleColor = $row['status'] === 'blocked' ? 'text-green-600' : 'text-red-600';
                        ?>
                        <a href="php/toggle_status.php?id=<?php echo $row['user_id']; ?>&action=<?php echo $toggleAction; ?>" 
                          class="block px-4 py-2 text-sm <?php echo $toggleColor; ?> hover:bg-gray-100">
                          <i class="fas <?php echo $toggleIcon; ?> mr-1"></i> <?php echo $toggleLabel; ?>
                        </a>

                    </div>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
   
        <p id="noResultsMessage" class="text-center text-gray-500 mt-4 hidden">No results found.</p>
        <div class="flex justify-center mt-6 space-x-2">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>"
              class="px-3 py-1 border rounded 
                      <?php echo ($i == $page) 
                        ? 'bg-blue-500 text-white' 
                        : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    </section>
  </main>
</div>
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-lg font-semibold mb-4">Edit User</h2>
        <form id="editUserForm" method="POST" action="php/update_user.php">
          <input type="hidden" name="user_id" id="editUserId">
          
          <label class="block mb-2 text-sm font-medium">Username</label>
          <input type="text" name="username" id="editUsername" class="w-full border rounded px-3 py-2 mb-4" required>

          <label class="block mb-2 text-sm font-medium">Phone</label>
            <input 
              type="text" 
              name="phone" 
              id="editPhone" 
              class="w-full border rounded px-3 py-2 mb-4" 
              required 
              maxlength="11" 
              pattern="\d{11}" 
              title="Phone number must be exactly 11 digits and contain digits only" 
              oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);" 
            />

          <label class="block mb-2 text-sm font-medium">Email</label>
          <input type="email" name="email" id="editEmail" class="w-full border rounded px-3 py-2 mb-4" required>

          <div class="flex justify-end gap-2">
            <button type="button" onclick="closeEditModal()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
          </div>
        </form>
      </div>
    </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
        <h2 class="text-lg font-bold text-gray-700">Success</h2>
        <p class="text-gray-700 mt-2"><?php echo $_SESSION['success']; ?></p>
        <button onclick="document.getElementById('successModal').classList.add('hidden')" class="mt-4 bg-slate-500 text-white px-4 py-2 rounded hover:bg-slate-600">OK</button>
      </div>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

<div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden items-center justify-center">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
    <h2 class="text-lg font-semibold text-gray-800">Confirm Archive</h2>
    <p class="text-gray-700 mt-2">Are you sure you want to archive this user?</p>
    <form id="archiveForm" method="GET" action="php/archive_user.php">
      <input type="hidden" name="id" id="archiveUserId">
      <div class="mt-4 flex justify-center gap-3">
        <button type="button" onclick="closeArchiveModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Yes</button>
      </div>
    </form>
  </div>
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
  
  function toggleDropdown(button) {
    const menu = button.nextElementSibling;
    document.querySelectorAll('.dropdown-menu').forEach(m => {
      if (m !== menu) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.text-left')) {
      document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
    }
  });

  function openEditModal(userId, username, phone, email) {
  document.getElementById('editUserId').value = userId;
  document.getElementById('editUsername').value = username;
  document.getElementById('editPhone').value = phone;
  document.getElementById('editEmail').value = email;
  document.getElementById('editUserModal').classList.remove('hidden');
  document.getElementById('editUserModal').classList.add('flex');
}

function closeEditModal() {
  document.getElementById('editUserModal').classList.add('hidden');
  document.getElementById('editUserModal').classList.remove('flex');
}

function openArchiveModal(userId) {
  document.getElementById('archiveUserId').value = userId;
  const modal = document.getElementById('archiveModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeArchiveModal() {
  const modal = document.getElementById('archiveModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

</script>
</body>
</html>
