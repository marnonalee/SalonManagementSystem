<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon";

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$total_result = $conn->query("SELECT COUNT(*) AS total FROM services WHERE is_archived = 0");
$total_row = $total_result->fetch_assoc();
$total_services = $total_row['total'];
$total_pages = ceil($total_services / $limit);

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
if ($success_message) {
    echo "
    <div id='successModal' class='fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 z-50'>
        <div class='bg-white text-gray-800 p-6 rounded-lg shadow-lg w-96'>
            <div class='flex items-center'>
                <i class='fas fa-check-circle mr-2 text-gray-500'></i>
                <span class='text-base'>$success_message</span>
            </div>
            <div class='mt-4 text-right'>
                <button id='closeSuccessModal' class='bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded'>
                  OK
                </button>
            </div>
        </div>
    </div>";
    unset($_SESSION['success_message']);
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
      body {
        font-family: 'Inter', sans-serif;
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
    <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
    <a class="sidebar-link" href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
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
            <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-cogs"></i> Service Management</h1>
            <input type="text" id="searchInput" placeholder="Search..." class="w-80 px-4 py-2 border rounded-md focus:outline-none" />
          <div class="flex justify-end">
            <button onclick="openServiceModal()" class="bg-black flex justify-end text-white text-sm px-4 py-2 rounded hover:bg-gray-800">
            <i class="fas fa-plus-circle mr-2 mt-1"></i> Add Service
            </button>
          </div>
        </header>
          
      <section>
          <div class="overflow-x-auto mt-2">
              
              <table class="min-w-full bg-white rounded-md shadow-md">
                  <thead class="bg-gray-200 text-gray-700 text-sm">
                      <tr>
                          <th class="text-left px-6 py-3">Service Name</th>
                          <th class="text-left px-6 py-3">Price</th>
                          <th class="text-left px-6 py-3">Time</th>
                          <th class="text-left px-6 py-3">Specialization</th>
                          <th class="text-left px-6 py-3">Appointment Fee</th>
                          <th class="text-left px-6 py-3">Actions</th>
                      </tr>
                  </thead>
                <tbody class="text-sm text-gray-800 divide-y divide-gray-100">
                    <?php
                    require_once '../db.php';
                    $sql = "SELECT * FROM services WHERE is_archived = 0 ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

                    $result = $conn->query($sql);
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                      
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['service_name']); ?></td>
                        <td class="px-6 py-4">
                          ₱<?php echo number_format($row['price'], 2); ?>
                          <?php 
                            if ($row['price_max'] > $row['price']) {
                              echo " - ₱" . number_format($row['price_max'], 2);
                            }
                          ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $duration_minutes = isset($row['duration']) ? $row['duration'] : 0;
                            if ($duration_minutes > 0) {
                                $hours = floor($duration_minutes / 60); 
                                $minutes = $duration_minutes % 60; 

                                $formatted_duration = "";
                                if ($hours > 0) {
                                    $formatted_duration .= $hours . " hr";
                                }
                                if ($minutes > 0) {
                                    if ($formatted_duration !== "") {
                                        $formatted_duration .= " ";
                                    }
                                    $formatted_duration .= $minutes . " mins";
                                }

                                echo htmlspecialchars($formatted_duration);
                            } else {
                                echo "No duration specified";
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $specialization = isset($row['specialization_required']) && !empty($row['specialization_required']) ? htmlspecialchars($row['specialization_required']) : 'No specialization required';
                            echo $specialization;
                            ?>
                        </td>
                        <td class="px-6 py-4">₱<?php echo number_format($row['appointment_fee'], 2); ?></td>
                        <td class="px-6 py-4">
                          <div class="flex items-center space-x-4">
                              <button type="button" 
                                      class="text-blue-500 hover:text-blue-700 text-lg" 
                                      onclick="openEditModal(<?php echo $row['service_id']; ?>)" 
                                      title="Edit Service">
                                  <i class="fas fa-pencil-alt"></i> 
                              </button>
                              <form method="POST" action="php/archive_service.php" class="archive-form">
                                  <input type="hidden" name="service_id" value="<?php echo $row['service_id']; ?>">
                                  <button type="button" 
                                      class="text-red-500 hover:text-red-700 text-lg archive-button" 
                                      data-service-id="<?php echo $row['service_id']; ?>"
                                      title="Archive Service">
                                      <i class="fas fa-archive"></i>
                                  </button>
                              </form>
                          </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                      <tr><td colspan="5" class="text-center py-4 text-gray-500">No services found.</td></tr>
                    <?php endif; ?>
                </tbody>
              </table>
              <p id="noResultsMessage" class="text-center text-gray-500 mt-4 hidden">No results found.</p>

              
              <?php if ($total_pages > 1): ?>
              <div class="mt-4 flex justify-center space-x-2 text-sm">
                  <?php if ($page > 1): ?>
                      <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Prev</a>
                  <?php endif; ?>

                  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                      <a href="?page=<?php echo $i; ?>" class="px-3 py-1 rounded <?php echo ($i === $page) ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                          <?php echo $i; ?>
                      </a>
                  <?php endfor; ?>

                  <?php if ($page < $total_pages): ?>
                      <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
                  <?php endif; ?>
              </div>
              <?php endif; ?>
          </div>
      </section>
    </main>
  </div>

    <div id="editModal" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 hidden z-50">
      
      <div class="bg-white rounded-lg p-6 max-w-md w-full relative z-60">
      <button id="closeEditModal" class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">&times;</button>

        <div id="modalContent"></div>
      </div>
    </div>
    <div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-2xl relative">
        <button onclick="closeServiceModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">&times;</button>
        <h2 class="text-lg font-semibold mb-4">Add New Service</h2>
        <form action="php/add_service.php" method="POST" enctype="multipart/form-data" class="space-y-6">
          
        <div>
          <label for="service-name" class="block text-sm font-medium text-gray-700">Service Name</label>
          <input type="text" id="service-name" name="service_name" required class="mt-1 block w-full px-4 py-2 border rounded-md" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" />
        </div>

            <div class="flex-1">
              <label for="price" class="block text-sm font-medium text-gray-700">Min Price</label>
              <input type="number" id="price" name="price" required min="0" step="0.01" class="mt-1 block w-full px-4 py-2 border rounded-md" />
            </div>
            <div class="flex-1">
              <label for="price_max" class="block text-sm font-medium text-gray-700">Max Price</label>
              <input type="number" id="price_max" name="price_max" required min="0" step="0.01" class="mt-1 block w-full px-4 py-2 border rounded-md" />
            </div>
      
          
          <div class="flex space-x-2">
            <div>
              <label for="duration_hours" class="block text-sm font-medium text-gray-700">Hours</label>
              <input type="number" id="duration_hours" name="duration_hours" class="mt-1 block w-full px-4 py-2 border rounded-md" min="0">
            </div>
            <div>
              <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Minutes</label>
              <input type="number" id="duration_minutes" name="duration_minutes" class="mt-1 block w-full px-4 py-2 border rounded-md" min="0" max="59">
            </div>
          </div>

          <div>
            <label for="specialization" class="block text-sm font-medium text-gray-700">Required Specialization</label>
            <select id="specialization" name="specialization" required class="mt-1 block w-full px-4 py-2 border rounded-md">
              <option value="">Select Specialization</option>
              <?php
                $specialization_query = "SELECT DISTINCT specialization FROM employees WHERE specialization IS NOT NULL AND specialization != ''";
                $specialization_result = $conn->query($specialization_query);

                if ($specialization_result && $specialization_result->num_rows > 0) {
                    while ($row = $specialization_result->fetch_assoc()) {
                        $specialization = htmlspecialchars($row['specialization']);
                        echo "<option value=\"$specialization\">$specialization</option>";
                    }
                } else {
                    echo "<option value=\"\">No specializations found</option>";
                }
              ?>
            </select>
          </div>
            <div class="flex justify-end space-x-4">
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-xs font-medium">Add Service</button>
              <button type="reset" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-xs font-medium">Clear</button>
            </div>
        </form>
      </div>
    </div>

<div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded-lg w-full max-w-md relative">
    <h2 class="text-lg font-semibold mb-4">Confirm Archive</h2>
    <p class="mb-6 text-sm text-gray-700">Are you sure you want to archive this service?</p>
    <div class="flex justify-end space-x-4">
      <button id="cancelArchive" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Cancel</button>
      <button id="confirmArchive" class="px-4 py-2 bg-red-600 text-white rounded">Archive</button>
    </div>
  </div>
</div>

<div id="updateSuccessModal" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 hidden z-50">
  <div class="bg-white text-gray-800 p-6 rounded-lg shadow-lg w-96">
    <div class="flex items-center">
      <i class="fas fa-check-circle mr-2 text-gray-500"></i>
      <span class="text-base">Updated successfully</span>
    </div>
    <div class="mt-4 text-right">
      <button id="closeUpdateSuccessModal" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded">
        OK
      </button>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
  document.body.addEventListener('submit', function (e) {
        if (e.target && e.target.id === 'editServiceForm') {
          e.preventDefault(); 

          const form = e.target;
          const formData = new FormData(form);

          fetch('php/update_service.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json()) 
          .then(data => {
            if (data.success) {
              document.getElementById('editModal').classList.add('hidden');
              const successModal = document.getElementById('updateSuccessModal');
              successModal.classList.remove('hidden');

              document.getElementById('closeUpdateSuccessModal').onclick = () => {
                successModal.classList.add('hidden');
                location.reload(); 
              };
              
              setTimeout(() => {
                successModal.classList.add('hidden');
                location.reload();
              }, 3000);

            } else {
              alert('Update failed: ' + (data.message || 'Unknown error'));
            }
          })
          .catch(err => {
            alert('Error submitting form: ' + err.message);
          });
        }
      });
    });

  document.addEventListener('DOMContentLoaded', function () {
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

    window.openEditModal = function (serviceId) {
      fetch('php/edit_service.php?id=' + serviceId)
        .then(response => response.text())
        .then(data => {
          document.getElementById('modalContent').innerHTML = data;
          document.getElementById('editModal').classList.remove('hidden');
        });
    };

    document.getElementById('closeEditModal')?.addEventListener('click', function () {
      document.getElementById('editModal').classList.add('hidden');
    });

    const successModal = document.getElementById('successModal');
    const closeSuccessBtn = document.getElementById('closeSuccessModal');
    if (successModal && closeSuccessBtn) {
      closeSuccessBtn.addEventListener('click', () => {
        successModal.style.display = 'none';
      });

      setTimeout(() => {
        successModal.style.display = 'none';
      }, 3000);
    }

    window.openServiceModal = function () {
      const modal = document.getElementById('serviceModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };

    window.closeServiceModal = function () {
      const modal = document.getElementById('serviceModal');
      modal.classList.remove('flex');
      modal.classList.add('hidden');
    };

    window.addEventListener('click', function (e) {
      const modal = document.getElementById('serviceModal');
      if (e.target === modal) closeServiceModal();
    });

    const serviceNameInput = document.getElementById('service-name');
    if (serviceNameInput) {
      serviceNameInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
      });
    }

    let selectedForm = null;

    document.querySelectorAll('.archive-button').forEach(button => {
      button.addEventListener('click', function () {
        selectedForm = this.closest('form');
        document.getElementById('archiveModal').classList.remove('hidden');
        document.getElementById('archiveModal').classList.add('flex');
      });
    });

    document.getElementById('cancelArchive').addEventListener('click', function () {
      document.getElementById('archiveModal').classList.remove('flex');
      document.getElementById('archiveModal').classList.add('hidden');
      selectedForm = null;
    });

    document.getElementById('confirmArchive').addEventListener('click', function () {
      if (selectedForm) selectedForm.submit();
    });

    document.getElementById('archiveModal').addEventListener('click', function (e) {
      if (e.target === this) {
        this.classList.remove('flex');
        this.classList.add('hidden');
      }
    });
  });

  
</script>

</body>
</html>
