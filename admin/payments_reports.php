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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_method'])) {
        $method_name = trim($_POST['method_name']);
        $details = trim($_POST['details']);
        $contact_number = trim($_POST['contact_number']);

        $qr_code_filename = null;
        if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $tmp_name = $_FILES['qr_code']['tmp_name'];
            $original_name = basename($_FILES['qr_code']['name']);
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $allowed)) {
                $qr_code_filename = uniqid('qr_') . '.' . $ext;
                $target_path = $upload_dir . $qr_code_filename;
                if (!move_uploaded_file($tmp_name, $target_path)) {
                    $qr_code_filename = null;
                }
            }
        }

        if ($method_name !== '' && $details !== '') {
          $stmt = $conn->prepare("INSERT INTO payment_methods (method_name, details, qr_code, contact_number, is_active) VALUES (?, ?, ?, ?, 1)");
          $stmt->bind_param("ssss", $method_name, $details, $qr_code_filename, $contact_number);
            $stmt->execute();
            $stmt->close();

            header("Location: payments_reports.php");
            exit();
        }
    }

    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['payment_method_id'];
        $current_status = (int)$_POST['current_status'];
        $new_status = $current_status === 1 ? 0 : 1;

        $stmt = $conn->prepare("UPDATE payment_methods SET is_active = ? WHERE payment_method_id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: payments_reports.php");
        exit();
    }

    if (isset($_POST['delete_method'])) {
        $id = (int)$_POST['payment_method_id'];

        $stmt = $conn->prepare("SELECT qr_code FROM payment_methods WHERE payment_method_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($qr_code_file);
        $stmt->fetch();
        $stmt->close();

        if ($qr_code_file) {
            $file_path = __DIR__ . '/uploads/' . $qr_code_file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $stmt = $conn->prepare("DELETE FROM payment_methods WHERE payment_method_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: payments_reports.php");
        exit();
    }
}
if (isset($_POST['edit_method'])) {
  $id = (int)$_POST['payment_method_id'];
  $method_name = trim($_POST['method_name']);
  $details = trim($_POST['details']);
  $contact_number = trim($_POST['contact_number']);

  $qr_code_filename = null;
  if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
      $upload_dir = __DIR__ . '/uploads/';
      if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0755, true);
      }
      $tmp_name = $_FILES['qr_code']['tmp_name'];
      $original_name = basename($_FILES['qr_code']['name']);
      $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
      $allowed = ['jpg', 'jpeg', 'png', 'gif'];

      if (in_array($ext, $allowed)) {
          $qr_code_filename = uniqid('qr_') . '.' . $ext;
          $target_path = $upload_dir . $qr_code_filename;
          if (!move_uploaded_file($tmp_name, $target_path)) {
              $qr_code_filename = null;
          }
      }
  }

  if ($method_name !== '') {
      if ($qr_code_filename) {
        $stmt = $conn->prepare("UPDATE payment_methods SET method_name = ?, details = ?, qr_code = ?, contact_number = ? WHERE payment_method_id = ?");
        $stmt->bind_param("ssssi", $method_name, $details, $qr_code_filename, $contact_number, $id);
    } else {
        $stmt = $conn->prepare("UPDATE payment_methods SET method_name = ?, details = ?, contact_number = ? WHERE payment_method_id = ?");
        $stmt->bind_param("sssi", $method_name, $details, $contact_number, $id);
    }
      $stmt->execute();
      $stmt->close();

      $_SESSION['update_success'] = true;
      header("Location: payments_reports.php");
      exit();
  }
}
if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
  $tmp_name = $_FILES['qr_code']['tmp_name'];
  $original_name = basename($_FILES['qr_code']['name']);
  $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
  $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

  if (in_array($ext, $allowed_ext)) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $tmp_name);
      finfo_close($finfo);

      $allowed_mime = ['image/jpeg', 'image/png', 'image/gif'];
      if (in_array($mime, $allowed_mime)) {
          $qr_code_filename = uniqid('qr_') . '.' . $ext;
          $target_path = $upload_dir . $qr_code_filename;

          if (move_uploaded_file($tmp_name, $target_path)) {
          } else {
              $qr_code_filename = null;
          }
      } else {
          echo "<script>alert('Invalid file type. Only QR code images are allowed.');</script>";
      }
  } else {
      echo "<script>alert('Only JPG, PNG, or GIF images are allowed for QR codes.');</script>";
  }
}

$methods_result = $conn->query("SELECT * FROM payment_methods ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - Payments & Reports</title>
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
            <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
            <a class="sidebar-link " href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
            <a class="sidebar-link" href="payments.php"><i class="fas fa-receipt"></i><span>Payment Records</span></a>
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span  class="whitespace-nowrap">Payment Methods</span></a>
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
        <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-file-invoice-dollar"></i> Payments Method List</h1>
        <div class="flex justify-end">
            <button id="openModalBtn" class="bg-black flex justify-end text-white text-sm px-4 py-2 rounded hover:bg-gray-800">
            <i class="fas fa-plus-circle mr-2 mt-1"></i> Add Payment Method
            </button>
          </div>
      </header>

      <section>
        
          <div class="bg-white shadow rounded-lg overflow-visible">
            <table class="min-w-full text-sm text-left text-gray-700">
              <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
                <tr>
                  <th class="text-left px-4 py-3 border-b">Name</th>
                  <th class="text-left px-4 py-3 border-b">Contact Number</th>
                  <th class="text-left px-4 py-3 border-b">Details</th>
                  <th class="text-center px-4 py-3 border-b">QR Code</th>
                  <th class="text-center px-4 py-3 border-b">Status</th>
                  <th class="text-center px-4 py-3 border-b"></th>
                </tr>
              </thead>
              <tbody>
                <?php if ($methods_result->num_rows > 0): ?>
                  <?php while ($row = $methods_result->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-50">
                      <td class="px-4 py-3 border-b"><?php echo htmlspecialchars($row['method_name']); ?></td>
                      <td class="px-4 py-3 border-b"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                      <td class="px-4 py-3 border-b"><?php echo nl2br(htmlspecialchars($row['details'])); ?></td>
                      <td class="text-center px-4 py-3 border-b">
                        <?php if (!empty($row['qr_code']) && file_exists(__DIR__ . '/uploads/' . $row['qr_code'])): ?>
                          <img src="<?php echo 'uploads/' . htmlspecialchars($row['qr_code']); ?>" alt="QR Code" class="mx-auto w-16 h-16 object-contain" />
                        <?php else: ?>
                          <span class="text-gray-400 italic text-xs">No QR code</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center px-4 py-3 border-b">
                        <?php if ($row['is_active'] == 1): ?>
                          <span class="inline-block px-3 py-1 text-xs rounded-full bg-green-200 text-green-800">Active</span>
                        <?php else: ?>
                          <span class="inline-block px-3 py-1 text-xs rounded-full bg-gray-300 text-gray-700">Disabled</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center px-4 py-3 border-b">
                        <div class="relative inline-block text-left">
                          <button onclick="toggleDropdown(this)" 
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Actions <i class="fas fa-chevron-down ml-1"></i>
                          </button>

                          <div class="dropdown-menu absolute right-0 mt-2 w-44 bg-white border rounded shadow-lg hidden z-50">
                   
                            <form method="post">
                              <input type="hidden" name="payment_method_id" value="<?php echo (int)$row['payment_method_id']; ?>">
                              <input type="hidden" name="current_status" value="<?php echo (int)$row['is_active']; ?>">
                              <button type="submit" name="toggle_status"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas <?php echo $row['is_active'] == 1 ? 'fa-toggle-off text-yellow-600' : 'fa-toggle-on text-green-600'; ?> mr-1"></i>
                                <?php echo $row['is_active'] == 1 ? 'Disable' : 'Enable'; ?>
                              </button>
                            </form>

                            <button 
                              data-id="<?php echo $row['payment_method_id']; ?>"
                              data-name="<?php echo htmlspecialchars($row['method_name'], ENT_QUOTES); ?>"
                              data-details="<?php echo htmlspecialchars($row['details'], ENT_QUOTES); ?>"
                              data-contact="<?php echo htmlspecialchars($row['contact_number'], ENT_QUOTES); ?>"
                              class="edit-btn block w-full text-left px-4 py-2 text-blue-700 hover:bg-gray-100">
                              <i class="fas fa-edit mr-1"></i> Edit
                            </button>

                            <form method="post" onsubmit="return confirm('Delete this payment method?');">
                              <input type="hidden" name="payment_method_id" value="<?php echo (int)$row['payment_method_id']; ?>">
                              <button type="submit" name="delete_method"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                              </button>
                            </form>
                          </div>
                        </div>
                      </td>

                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-center py-6 text-gray-500">No payment methods found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
      </section>
    </main>
  </div>
  <div id="addModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
      <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
      <h3 class="text-xl font-semibold mb-4">Add Payment Method</h3>
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label for="method_name" class="block font-medium text-gray-700">Method Name <span class="text-red-600">*</span></label>
          <input type="text" id="method_name" name="method_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
        </div>
        <div>
          <label for="contact_number" class="block font-medium text-gray-700">Account Number<span class="text-red-600">*</span></label>
          <input type="text" id="contact_number" name="contact_number"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                maxlength="11" pattern="\d{11}" inputmode="numeric"
                title="Please enter a valid 11-digit number" required />
        </div>

        <div>
          <label for="details" class="block font-medium text-gray-700" required>Details <span class="text-red-600">*</span></label>
          <textarea id="details" name="details" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required></textarea>
        </div>
        <div>
          <label for="qr_code" class="block font-medium text-gray-700">QR Code Image (optional)</label>
          <input type="file" id="qr_code" name="qr_code" accept="image/*" class="mt-1 block w-full" />
        </div>
        <div class="flex justify-end">
          <button type="submit" name="add_method" class="bg-gray-500 text-white px-5 py-2 rounded hover:bg-gray-600 font-semibold">Add</button>
        </div>
      </form>
    </div>
  </div>


  <div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
      <button id="closeEditModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
      <h3 class="text-xl font-semibold mb-4">Edit Payment Method</h3>
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="payment_method_id" id="edit_payment_method_id" />
        <div>
          <label for="edit_method_name" class="block font-medium text-gray-700">Method Name <span class="text-red-600">*</span></label>
          <input type="text" id="edit_method_name" name="method_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
        </div>
        <div>
          <label for="edit_contact_number" class="block font-medium text-gray-700">Account Number<span class="text-red-600">*</span></label>
          <input type="text" id="edit_contact_number" name="contact_number"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                maxlength="11" pattern="\d{11}" inputmode="numeric"
                title="Please enter a valid 11-digit number" required />
        </div>


        <div>
          <label for="edit_details" class="block font-medium text-gray-700" required>Details</label>
          <textarea id="edit_details" name="details" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
        </div>
        <div>
          <label for="edit_qr_code" class="block font-medium text-gray-700">QR Code Image</label>
          <input type="file" id="edit_qr_code" name="qr_code" accept="image/png, image/jpeg, image/jpg, image/gif" />
                          
        </div>
        <div class="flex justify-end">
          <button type="submit" name="edit_method" class="bg-blue-500 text-white px-5 py-2 rounded hover:bg-blue-600 font-semibold">Update</button>
        </div>
      </form>
    </div>
  </div>

<div id="successModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6 relative text-center">
    <button id="closeSuccessModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
    <h3 class="text-xl font-semibold mb-4">Update Successfully</h3>
    <button id="successOkBtn" class="bg-slate-500 text-white px-5 py-2 rounded hover:bg-slate-600 font-semibold">OK</button>
  </div>
</div>

  <script>
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const addModal = document.getElementById('addModal');

    openModalBtn.addEventListener('click', () => {
      addModal.classList.remove('hidden');
      addModal.classList.add('flex');
    });

    closeModalBtn.addEventListener('click', () => {
      addModal.classList.remove('flex');
      addModal.classList.add('hidden');
    });

    addModal.addEventListener('click', (e) => {
      if (e.target === addModal) {
        addModal.classList.remove('flex');
        addModal.classList.add('hidden');
      }
    });


    const editModal = document.getElementById('editModal');
  const closeEditModalBtn = document.getElementById('closeEditModalBtn');
  document.querySelectorAll('.edit-btn').forEach(button => {
  button.addEventListener('click', () => {
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const details = button.getAttribute('data-details');
    const contact_number = button.getAttribute('data-contact');

    document.getElementById('edit_payment_method_id').value = id;
    document.getElementById('edit_method_name').value = name;
    document.getElementById('edit_details').value = details;
    document.getElementById('edit_contact_number').value = contact_number;

    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
  });
});


  closeEditModalBtn.addEventListener('click', () => {
    editModal.classList.remove('flex');
    editModal.classList.add('hidden');
  });

  editModal.addEventListener('click', (e) => {
    if (e.target === editModal) {
      editModal.classList.remove('flex');
      editModal.classList.add('hidden');
    }
  });
 

  function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    allDropdowns.forEach(menu => {
      if (menu !== dropdown) {
        menu.classList.add('hidden');
      }
    });
    dropdown.classList.toggle('hidden');
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.relative.inline-block')) {
      document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.add('hidden');
      });
    }
  });

  function openEditModal(id, name, details, contact_number) {
  document.getElementById('edit_payment_method_id').value = id;
  document.getElementById('edit_method_name').value = name;
  document.getElementById('edit_details').value = details;
  document.getElementById('edit_contact_number').value = contact_number;
  document.getElementById('editModal').classList.remove('hidden');
  document.getElementById('editModal').classList.add('flex');
}

function restrictToDigits(inputId) {
    const input = document.getElementById(inputId);
    input.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });
  }

  restrictToDigits('contact_number');
  restrictToDigits('edit_contact_number');



  document.addEventListener('DOMContentLoaded', () => {
  const editModal = document.getElementById('editModal');
  const successModal = document.getElementById('successModal');
  const closeEditModalBtn = document.getElementById('closeEditModalBtn');
  const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');
  const successOkBtn = document.getElementById('successOkBtn');
  const editForm = editModal.querySelector('form');

  closeEditModalBtn.addEventListener('click', () => {
    editModal.classList.add('hidden');
  });

  const closeSuccess = () => {
    successModal.classList.add('hidden');
  };
  closeSuccessModalBtn.addEventListener('click', closeSuccess);
  successOkBtn.addEventListener('click', closeSuccess);


});

</script>


</body>
</html>
