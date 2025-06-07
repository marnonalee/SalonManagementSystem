<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
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
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="employees.php"><i class="fas fa-user-tie"></i><span>Employees</span></a>
            <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
            <a class="sidebar-link " href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
            <a class="sidebar-link" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
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
        <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-user-tie"></i> Employees</h1>
        <input type="text" id="searchInput" placeholder="Search..." class="w-80 px-4 py-2 border rounded-md focus:outline-none" />
        
            <div class="flex justify-end">
                <button id="addEmployeeBtn" class="bg-black flex justify-end text-white text-sm px-4 py-2 rounded hover:bg-gray-800">
                <i class="fas fa-plus-circle mr-2 mt-1"></i> Add Employee
                </button>
            </div>
    </header>
          
    <section>
      <div class="flex justify-between mb-6">
        <h2 class="flex items-center text-gray-900 font-semibold text-sm">
          <i class="fas fa-user-tie mr-2 text-gray-700 text-[14px]"></i> Employee List
        </h2>
      </div>

      <div class="overflow-x-auto rounded-lg border border-gray-100">
       
        <table class="w-full text-xs text-left text-gray-600">
        <thead class="bg-gray-200 text-gray-600 font-semibold">
          <tr>
            <th class="px-4 py-3">EMPLOYEE ID</th>
            <th class="px-4 py-3">NAME</th>
            <th class="px-4 py-3">SPECIALIZATION</th>
            <th class="px-4 py-3">EMAIL</th>
            <th class="px-4 py-3">STATUS</th>
            <th class="px-4 py-3">START TIME</th>
            <th class="px-4 py-3">END TIME</th>
            <th class="px-4 py-3">ACTIONS</th>
 
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php
          include '../db.php';
          $result = $conn->query("SELECT * FROM employees WHERE status != 'inactive' ORDER BY created_at ASC");

          if ($result->num_rows > 0):
              $counter = 1;
              while ($row = $result->fetch_assoc()):
          ?>
          <tr>
            <td class="px-4 py-3"><?php echo $counter++; ?></td>
            <td class="px-4 py-3"><?php echo $row['name']; ?></td>
            <td class="px-4 py-3"><?php echo $row['specialization']; ?></td>
            <td class="px-4 py-3"><?php echo $row['email']; ?></td>
            <td class="px-4 py-3"><?php echo $row['status']; ?></td>
            <td class="px-4 py-3">
              <?php echo date("g:i A", strtotime($row['start_time'])); ?>
            </td>
            <td class="px-4 py-3">
              <?php echo date("g:i A", strtotime($row['end_time'])); ?>
            </td>
          
            <td class="px-4 py-3">
              <button class="text-yellow-600" onclick="editEmployee(<?php echo $row['employee_id']; ?>)" title="Edit">
                <i class="fas fa-edit fa-lg"></i>
              </button>
              <button class="text-red-600 ml-2" onclick="archiveEmployee(<?php echo $row['employee_id']; ?>, this.closest('tr'))" title="Archive">
                <i class="fas fa-archive fa-lg"></i>
              </button>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr>
            <td colspan="7" class="px-4 py-3 text-center text-gray-400">No employees found.</td>
          </tr>
          <?php endif; ?>
        </tbody>
        </table>
        <p id="noResultsMessage" class="text-center text-gray-500 mt-4 hidden">No results found.</p>
      </div>
      <div class="mt-4 flex justify-end">
        <a href="php/download_employees.php" 
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition">
          <i class="fas fa-download mr-2"></i> Download Employee List
        </a>
      </div>

    </section>

    <section class="mt-8">
      <h2 class="flex items-center text-gray-900 font-semibold text-sm">
          <i class="fas fa-archive mr-2 text-gray-700 text-[14px]"></i> Archived Employees
      </h2>
      <div class="overflow-x-auto rounded-lg border border-gray-100 mt-4">
        <table class="w-full text-xs text-left text-gray-600">
          <thead class="bg-gray-200 text-gray-600 font-semibold">
            <tr>
              <th class="px-4 py-3">EMPLOYEE ID</th>
              <th class="px-4 py-3">NAME</th>
              <th class="px-4 py-3">SPECIALIZATION</th>
              <th class="px-4 py-3">EMAIL</th>
              <th class="px-4 py-3">STATUS</th>
              <th class="px-4 py-3">ACTIONS</th>
            <th class="px-4 py-3">START TIME</th>
            <th class="px-4 py-3">END TIME</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php
            include '../db.php';
            $result = $conn->query("SELECT * FROM employees WHERE status = 'inactive' ORDER BY created_at ASC");

            if ($result->num_rows > 0):
                $counter = 1;
                while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td class="px-4 py-3"><?php echo $counter++; ?></td>
              <td class="px-4 py-3"><?php echo $row['name']; ?></td>
              <td class="px-4 py-3"><?php echo $row['specialization']; ?></td>
              <td class="px-4 py-3"><?php echo $row['email']; ?></td>
              <td class="px-4 py-3"><?php echo $row['status']; ?></td>
              <td class="px-4 py-3"><?php echo $row['start_time']; ?></td>
              <td class="px-4 py-3"><?php echo $row['end_time']; ?></td>
              <td class="px-4 py-3">
                  <form action="php/restore_employee.php" method="POST" style="display:inline-block; margin-right: 10px;">
                    <input type="hidden" name="employee_id" value="<?php echo $row['employee_id']; ?>">
                    <button type="button" class="text-blue-600" 
                            onclick="restoreEmployee(<?php echo $row['employee_id']; ?>, this.closest('tr'))" title="Restore">
                      <i class="fas fa-undo-alt fa-lg"></i>
                    </button>
                  </form>
                  <button onclick="confirmDelete(<?php echo $row['employee_id']; ?>)" class="text-red-600" title="Delete">
                    <i class="fas fa-trash-alt fa-lg"></i>
                  </button>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="7" class="px-4 py-3 text-center text-gray-400">No archived employees found.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

    <div id="addEmployeeModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-30 flex justify-center items-center">
      <div class="bg-white p-6 rounded-lg w-96">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Add New Employee</h3>
        <form action="php/add_employee_handler.php" method="POST">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          </div>
          <div class="mb-4">
            <label for="specialization" class="block text-sm font-medium text-gray-700">Specialization</label>
            <input 
              type="text" 
              id="specialization" 
              name="specialization" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md" 
              placeholder="Enter Specialization" 
              required
            />
          </div>

          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          </div>

          <div class="mb-4">
            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
            <input type="time" id="start_time" name="start_time" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
            <p class="text-sm text-gray-500 mt-1">Preview: <span id="startTimePreview"></span></p>
          </div>

          <div class="mb-4">
            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
            <input type="time" id="end_time" name="end_time" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
            <p class="text-sm text-gray-500 mt-1">Preview: <span id="endTimePreview"></span></p>
          </div>

          <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Generated Password</label>
            <input type="text" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly required />
          </div>
          <div class="flex justify-between">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Add Employee</button>
            <button type="button" id="closeModalBtn" class="bg-gray-300 text-black px-4 py-2 rounded-md">Close</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editEmployeeModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-30 flex justify-center items-center">
      <div class="bg-white p-6 rounded-lg w-96">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Edit Employee</h3>
        <form id="editEmployeeForm" action="php/edit_employee_handler.php" method="POST">
          <input type="hidden" id="edit_employee_id" name="employee_id" />
          <div class="mb-4">
            <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="edit_name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          </div>
          <div class="mb-4">
            <label for="edit_specialization" class="block text-sm font-medium text-gray-700">Specialization</label>
            <input 
              type="text" 
              id="edit_specialization" 
              name="specialization" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md" 
              placeholder="Enter Specialization" 
              required
            />
          </div>

          <div class="mb-4">
            <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="edit_email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          </div>
          <div class="mb-4">
            <label for="edit_start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
            <input type="time" id="edit_start_time" name="start_time" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          </div>
          <div class="mb-4">
            <label for="edit_end_time" class="block text-sm font-medium text-gray-700">End Time</label>
            <input type="time" id="edit_end_time" name="end_time" class="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          </div>
          <div class="flex justify-end">
            <button type="button" onclick="closeEditModal()" class="mr-2 px-4 py-2 border rounded text-gray-700">Cancel</button>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-30 flex justify-center items-center">
      <div class="bg-white p-6 rounded-lg w-96">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Success</h3>
        <p class="text-gray-700 mb-4">Employee information updated successfully!</p>
        <button id="closeSuccessModalBtn" class="bg-slate-600 text-white px-4 py-2 rounded-md">Ok</button>
      </div>
    </div>


    <div id="employeeArchiveModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); 
        justify-content:center; align-items:center; z-index:1000;">
      <div style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.3); max-width:300px; text-align:center;">
        <p class="text-gray-700 mb-4">Employee archived successfully.</p>
        <button id="closeArchiveModalBtn" class="bg-slate-600 text-white px-4 py-2 rounded-md">OK</button>
      </div>
    </div>

    <div id="employeeRestoreModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); 
        justify-content:center; align-items:center; z-index:1000;">
      <div style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.3); max-width:300px; text-align:center;">
        <p class="text-gray-700 mb-4">Employee restored successfully.</p>
        <button id="closeRestoreModalBtn" class="bg-slate-600 text-white px-4 py-2 rounded-md">OK</button>
      </div>
    </div>

    <div id="confirmDeleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
      <h3 class="text-xl font-semibold mb-4">Confirm Deletion</h3>
      <p class="text-gray-700 mb-6">Are you sure you want to delete this employee?</p>
      <form id="deleteEmployeeForm" action="php/delete_employee.php" method="POST">
        <input type="hidden" name="employee_id" id="delete_employee_id">
        <div class="flex justify-end gap-3">
          <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Yes</button>
          <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border rounded text-gray-700">No</button>
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

      const addEmployeeBtn = document.getElementById('addEmployeeBtn');
      const addEmployeeModal = document.getElementById('addEmployeeModal');
      const closeModalBtn = document.getElementById('closeModalBtn');
      const passwordInput = document.getElementById('password');

      function generateStructuredPassword() {
        const letters = "abcdefghijklmnopqrstuvwxyz";
        const randomLetter = () => letters[Math.floor(Math.random() * letters.length)];
        const randomDigit = () => Math.floor(Math.random() * 10);
        return `salon${randomLetter()}${randomDigit()}${randomLetter()}${randomLetter()}`;
      }

      addEmployeeBtn.addEventListener('click', () => {
        const generatedPassword = generateStructuredPassword();
        passwordInput.value = generatedPassword;
        addEmployeeModal.classList.remove('hidden');
      });

      closeModalBtn.addEventListener('click', () => {
        addEmployeeModal.classList.add('hidden');
      });

      window.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
          addEmployeeModal.classList.add('hidden');
        }
      });

      function archiveEmployee(employeeId, row) {
        fetch('php/archive_employee_handler.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'employee_id=' + employeeId
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            showSuccessModal();
            row.remove();
          } else {
            alert("Failed to archive employee.");
          }
        })
        .catch(() => alert("Network error."));
      }

      function showSuccessModal() {
        const modal = document.getElementById('employeeArchiveModal');
        modal.style.display = 'flex';

        document.getElementById('closeArchiveModalBtn').onclick = () => {
          modal.style.display = 'none';
          location.reload();
        };
      }

      function restoreEmployee(employeeId, row) { 
        fetch('php/restore_employee.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'employee_id=' + employeeId
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            showRestoreModal();
            row.remove();  
          } else {
            alert("Failed to restore employee.");
          }
        })
        .catch(() => alert("Network error."));
      }

      function showRestoreModal() {
        const modal = document.getElementById('employeeRestoreModal');
        modal.style.display = 'flex';

        document.getElementById('closeRestoreModalBtn').onclick = () => {
          modal.style.display = 'none';
          location.reload();
        };
      }

      function editEmployee(id) {
      const row = document.querySelector(`button[onclick="editEmployee(${id})"]`).closest('tr');

      const name = row.children[1].textContent.trim();
      const specialization = row.children[2].textContent.trim();
      const email = row.children[3].textContent.trim();
      const startTime = convertTo24Hour(row.children[5].textContent.trim());
      const endTime = convertTo24Hour(row.children[6].textContent.trim());

      document.getElementById('edit_employee_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_specialization').value = specialization;
      document.getElementById('edit_email').value = email;
      document.getElementById('edit_start_time').value = startTime;
      document.getElementById('edit_end_time').value = endTime;

      document.getElementById('editEmployeeModal').classList.remove('hidden');
    }

    function convertTo24Hour(timeStr) {
      const [time, modifier] = timeStr.split(' ');
      let [hours, minutes] = time.split(':');
      if (hours === '12') hours = '00';
      if (modifier === 'PM') hours = parseInt(hours, 10) + 12;
      return `${hours.toString().padStart(2,'0')}:${minutes}`;
    }

    function closeEditModal() {
      document.getElementById('editEmployeeModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

      if (status === 'success') {
        const successModal = document.getElementById('successModal');
        successModal.classList.remove('hidden');

        document.getElementById('closeSuccessModalBtn').onclick = () => {
          successModal.classList.add('hidden');
          const cleanUrl = window.location.origin + window.location.pathname;
          window.history.replaceState({}, document.title, cleanUrl);
        };
      }
    });
      function showSuccessModal() {
      const modal = document.getElementById('employeeArchiveModal');
      modal.style.display = 'flex';

      document.getElementById('closeArchiveModalBtn').onclick = () => {
        modal.style.display = 'none';
        window.location.href = window.location.pathname;
      };
    }

    if (closeSuccessModalBtn) {
      closeSuccessModalBtn.addEventListener('click', () => {
        document.getElementById('successModal').classList.add('hidden');
      });
    }

    window.addEventListener('click', (e) => {
      if (e.target === document.getElementById('successModal')) {
        document.getElementById('successModal').classList.add('hidden');
      }
    });

    function formatAMPM(timeStr) {
      const [hour, minute] = timeStr.split(':');
      let h = parseInt(hour);
      const ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12 || 12;
      return `${h}:${minute} ${ampm}`;
    }

    document.getElementById('start_time').addEventListener('input', function() {
      document.getElementById('startTimePreview').textContent = formatAMPM(this.value);
    });

    document.getElementById('end_time').addEventListener('input', function() {
      document.getElementById('endTimePreview').textContent = formatAMPM(this.value);
    });

    function confirmDelete(employeeId) {
    document.getElementById('delete_employee_id').value = employeeId;
    document.getElementById('confirmDeleteModal').classList.remove('hidden');
  }
  function closeDeleteModal() {
    document.getElementById('confirmDeleteModal').classList.add('hidden');
  }
</script>

</body>
</html>
