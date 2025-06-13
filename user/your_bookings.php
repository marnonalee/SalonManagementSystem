<?php
include '../db.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;


if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

$username = $_SESSION['user']; 


$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$payment_methods = [];
$query = "SELECT * FROM payment_methods WHERE is_active = 1";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $payment_methods[] = $row;
}

$sql = "SELECT 
    a.appointment_id, 
    a.user_id, 
    s.service_name AS service, 
    s.appointment_fee AS service_fee,
    a.appointment_date, 
    a.start_time, 
    p.payment_status, 
    a.appointment_status,
    e.employee_id AS employee_id,
    e.name AS employee
FROM appointments a
JOIN services s ON a.service_id = s.service_id
JOIN employees e ON a.employee_id = e.employee_id
LEFT JOIN payments p ON a.appointment_id = p.appointment_id
WHERE a.user_id = $user_id AND a.is_deleted = 0
ORDER BY a.appointment_date DESC
LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$countSql = "SELECT COUNT(*) as total FROM appointments WHERE user_id = $user_id AND is_deleted = 0";
$countResult = mysqli_query($conn, $countSql);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adore & Beauty - Bookings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/your_booking.js" defer></script>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
</head>
<body class="font-[Poppins]">
    <aside class="bg-[#1d3239] w-64 min-h-screen shadow-lg fixed top-0 left-0 px-6 py-10 space-y-6 hidden md:block z-50 text-[#f3f4f6]">
        <div class="absolute top-0 right-0 h-full w-8 bg-[#ffb199] rounded-l-full z-0"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-10">
              <div class="flex items-center space-x-2">
                <img src="../images/logo1.png" alt="Adore & Beauty Logo" class="w-8" />
                <p class="text-white text-sm">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($username); ?></span>!</p>
              </div>
            </div>
                <nav class="flex flex-col space-y-4">
                    <a href="services.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-cut mr-3"></i>Services</a>
                    <a href="your_bookings.php"  class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition"><i class="fas fa-calendar-alt mr-3"></i>My Bookings</a>
                    <a href="messages_page.php"class="hover:text-[#fe7762] flex items-center transition" ><i class="fas fa-user mr-3"></i>Messages</a>
                    <div class="border-t border-[#334155] pt-4 mt-4">
                        <a href="profile.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-user mr-3"></i>Profile</a>
                        <a href="logout.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a>
                    </div>
                </nav>
          </div>
    </aside>

    <header class="fixed top-0 left-64 right-0 bg-white z-40 px-6 py-4 shadow-sm">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-black">My Appointments</h1>
        <input type="text" id="searchInput" placeholder="Search..." class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#1d3239] focus:border-[#1d3239] text-[#1d3239]"oninput="displayBookings()"/>
      </div>
    </header>
    <main class="md:ml-64 px-4 sm:px-8 pt-28 bg-gray-50 min-h-screen">
  <div class="flex justify-start space-x-4 mb-4">
    <button onclick="filterBookings('all')" class="tab-button active-tab">All</button>
    <button onclick="filterBookings('pending')" class="tab-button">Pending</button>
    <button onclick="filterBookings('accepted')" class="tab-button">Accepted</button>
    <button onclick="filterBookings('cancelled')" class="tab-button">Cancelled</button>
  </div>

  <div class="overflow-x-visible rounded-lg shadow-md border border-gray-200">
    <table class="w-full table-auto text-sm text-left text-gray-700 bg-white">
      <thead class="text-xs uppercase bg-gray-100 text-gray-600">
        <tr>
          <th class="py-3 px-6">#</th>
          <th class="py-3 px-6">Service</th>
          <th class="py-3 px-6">Date</th>
          <th class="py-3 px-6">Time</th>
          <th class="py-4 px-6">Payment</th>
          <th class="py-3 px-6">Approval</th>
          <th class="py-3 px-6">Employee</th>
          <th class="py-3 px-6 text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="bookingList" class="text-sm">
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <?php
            date_default_timezone_set('Asia/Manila');
            $now = time();
            $appointmentDateTime = strtotime($row['appointment_date'] . ' ' . $row['start_time']);

            $status = $row['appointment_status'];
            $statusLower = strtolower($status);

            $paymentStatus = $row['payment_status'] ?? 'No Payment';
            $paymentColors = [
              'Approved' => 'green',
              'Pending' => 'yellow',
              'Rejected' => 'red',
              'No Payment' => 'gray',
              'Paid'  => 'green',
              'Pending verification'  => 'gray',
              'Unpaid'  => 'orange'
            ];
            $color = $paymentColors[$paymentStatus] ?? 'gray';

            $displayStatus = $status;
            $statusColor = 'gray';
            if ($status === 'Upcoming') {
                $displayStatus = 'Accepted';
                $statusColor = 'green';
                $statusLower = 'accepted'; 
            } elseif ($status === 'Accepted') {
                $statusColor = 'green';
                $statusLower = 'accepted';
            } elseif ($status === 'Pending') {
                $statusColor = 'yellow';
            } elseif ($status === 'Cancelled') {
                $statusColor = 'red';
            }

            $cancelledAt = isset($row['cancelled_at']) ? strtotime($row['cancelled_at']) : null;
            $disableReschedule = false;
            if ($statusLower === 'cancelled' && $cancelledAt !== null) {
                $secondsSinceCancel = $now - $cancelledAt;
                if ($secondsSinceCancel <= 24 * 60 * 60) { 
                    $disableReschedule = true;
                }
            }
            $gracePeriod = 24 * 60 * 60;
            $approvalCompleteStatuses = ['approved', 'completed'];
            $disableCancel = in_array($statusLower, $approvalCompleteStatuses) || ($statusLower === 'cancelled') || (($appointmentDateTime - $now) <= $gracePeriod);

            $paymentStatusLower = strtolower($paymentStatus);
            $disablePayNow = in_array($paymentStatusLower, ['pending verification', 'paid']) || $statusLower === 'cancelled';
            $disableArchive = in_array($paymentStatusLower, ['pending', 'pending verification']);
          ?>
          <tr id="appointment-<?= $row['appointment_id'] ?>" data-status="<?= $statusLower ?>" class="bg-gray-50">
            <td class="py-2 px-4 border-b number-cell"></td>
            <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['service']) ?></td>
            <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td class="py-2 px-4 border-b"><?= date("g:i A", strtotime($row['start_time'])) ?></td>
            <td class="py-2 px-4 border-b">
              <div class="flex flex-col items-start">
                <span class="bg-<?= $color ?>-100 text-<?= $color ?>-700 py-1 px-3 rounded-full text-xs font-semibold">
                  <?= htmlspecialchars($paymentStatus) ?>
                </span>
              </div>
            </td>
            <td class="py-2 px-4 border-b">
              <span class="bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-700 py-1 px-3 rounded-full text-xs font-semibold">
                <?= $displayStatus === 'Cancelled' ? htmlspecialchars($row['cancel_reason'] ?? 'Cancelled') : htmlspecialchars($displayStatus) ?>
              </span>
            </td>
            <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['employee']) ?></td>
            <td class="py-2 px-4 border-b text-center relative">
              <div class="relative inline-block text-left">
                <button onclick="toggleDropdown(this)" class="text-gray-600 hover:text-black focus:outline-none">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-10">
                  <button onclick="<?= !$disableCancel ? "openCancelModal(" . $row['appointment_id'] . ")" : "event.preventDefault()" ?>" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 <?= $disableCancel ? 'text-gray-400 cursor-not-allowed' : 'text-red-600' ?>" <?= $disableCancel ? 'disabled' : '' ?>>
                    <i class="fas fa-times-circle mr-1"></i> Cancel
                  </button>
                  <button onclick="<?= !$disablePayNow ? "openPaymentMethodModal(" . $row['appointment_id'] . ", " . $row['service_fee'] . ")" : "event.preventDefault()" ?>" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 <?= $disablePayNow ? 'text-gray-400 cursor-not-allowed' : 'text-green-600' ?>" <?= $disablePayNow ? 'disabled' : '' ?>>
                    <i class="fas fa-money-bill-wave mr-1"></i> Pay Now
                  </button>
                  <button onclick="<?= !$disableArchive ? "archiveBooking(" . htmlspecialchars($row['appointment_id']) . ")" : "event.preventDefault()" ?>" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 <?= $disableArchive ? 'text-gray-400 cursor-not-allowed' : 'text-yellow-600' ?>" <?= $disableArchive ? 'disabled' : '' ?>>
                    <i class="fas fa-archive mr-1"></i> Archive
                  </button>
                  <button onclick="openMessageModal(<?= $row['appointment_id'] ?>)" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 text-indigo-600">
                    <i class="fas fa-envelope mr-2"></i> Message
                  </button>
                  <button onclick="<?= !$disableReschedule ? "openRescheduleModal(" . $row['appointment_id'] . ", '" . $row['appointment_date'] . "', '" . $row['start_time'] . "', " . $row['employee_id'] . ", '" . addslashes($row['employee']) . "')" : "event.preventDefault()" ?>" class="block w-full text-left px-3 py-2 text-sm hover:bg-gray-100 <?= $disableReschedule ? 'text-black cursor-not-allowed' : 'text-blue-600' ?> inline-flex items-center" <?= $disableReschedule ? 'disabled' : '' ?>>
                    <i class="fas fa-calendar mr-2"></i> Reschedule
                  </button>
                </div>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-4 text-gray-500">No bookings found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <p id="noBookingsMessage" class="text-center text-gray-500 mt-6 hidden">No bookings found.</p>

  <div class="flex justify-center items-center space-x-2 mt-6">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">&laquo; Prev</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>" class="px-3 py-1 rounded <?= $i == $page ? 'bg-[#fe7762] text-white' : 'bg-gray-100 hover:bg-gray-200' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Next &raquo;</a>
    <?php endif; ?>
  </div>

    <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-white rounded-lg w-96 p-6 relative shadow-lg">
        <button onclick="closeMessageModal()" class="absolute top-2 right-2 text-gray-500 hover:text-black text-2xl">&times;</button>
        <h2 class="text-xl font-semibold mb-4" id="modalEmployeeName">Message Employee</h2>
        <textarea id="messageText" rows="5" class="w-full p-2 border border-gray-300 rounded mb-4" placeholder="Type your message here..."></textarea>
        <button onclick="sendMessage()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Send</button>
      </div>
    </div>

    <div id="paymentMethodModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-[420px] max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4 text-center">Choose Payment Method</h2>
        <div class="space-y-4">
          <?php foreach ($payment_methods as $method): ?>
            
            <button onclick='selectPaymentMethod(<?= json_encode($method) ?>)'
              class="w-full text-left border p-4 rounded hover:bg-gray-100 transition">
              <div class="font-semibold"><?= htmlspecialchars($method['method_name']) ?></div>
            </button>
          <?php endforeach; ?>
        </div>
        <button onclick="closePaymentModal()" class="mt-4 w-full  py-2 rounded  bg-[#1d3239] text-white rounded hover:bg-[#121f28]">Cancel</button>
      </div>
    </div>

    <div id="paymentInfoModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-[420px]">
        <h2 class="text-xl font-semibold mb-4 text-center">Payment Details</h2>

        <p class="mb-2"><strong>Appointment Fee:</strong> ₱<span id="methodAppointmentFee"></span></p>
        <p class="mb-2"><strong>Method:</strong> <span id="methodName"></span></p>
        <p class="mb-2"><strong>Contact Number:</strong> <span id="methodContactNumber"></span></p>
        <p class="mb-4 text-sm text-gray-600" id="methodDetails"></p>

        <img id="qrCodeImg" src="" alt="QR Code" class="w-48 h-48 mx-auto mb-4 object-contain border rounded">

        <label class="block text-sm font-medium mb-2">Upload Screenshot:</label>
        <input type="file" name="payment_screenshot" accept="image/*" class="w-full mb-4" />

        <div class="flex justify-between">
          <button onclick="closePaymentInfoModal()" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          <button id="submitPaymentBtn" class="text-white px-4 py-2 bg-[#1d3239] rounded hover:bg-[#121f28]" disabled>Submit Payment</button>
        </div>
      </div>
    </div>

    <div id="invalidFileModal" class="fixed inset-0 flex items-center justify-center bg-[#1d3239cc] bg-opacity-50 hidden z-[100]">
      <div class="bg-white p-6 rounded-lg w-[300px] text-center">
        <h3 class="text-lg font-semibold mb-4">Invalid File Type</h3>
        <p class="mb-4">Please upload an image file only (jpg, jpeg, png, gif).</p>
        <button onclick="closeInvalidFileModal()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">OK</button>
      </div>
    </div>

    <div id="cancelModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h2 class="text-lg font-semibold mb-4">Cancel Booking</h2>
        <p class="mb-6">Are you sure you want to cancel this booking?</p>
        <div class="flex justify-end space-x-4">
          <button onclick="closeCancelModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">No</button>
          <button id="confirmCancelBtn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded">Yes, Cancel</button>
        </div>
      </div>
    </div>

    <div id="successModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-sm shadow-lg text-center">
        <h2 class="text-lg font-semibold mb-4  text-[#1d3239]">Success</h2>
        <p class="mb-4" id="successMessage">Appointment cancelled successfully.</p>
        <button onclick="closeSuccessModal()" class="px-4 py-2 bg-[#1d3239] text-white px-4 py-2 rounded hover:bg-[#121f28]">
          OK
        </button>
      </div>
    </div>

    <div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6">
        <h2 class="text-lg font-semibold mb-4">Confirm Archive</h2>
        <p class="mb-6">Are you sure you want to archive this booking?</p>
        <div class="flex justify-end space-x-4">
          <button id="cancelArchiveBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
          <button id="confirmArchiveBtn" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Yes</button>
        </div>
      </div>
    </div>

    <div id="successToast" class="fixed bottom-5 right-5 bg-green-600 text-white px-6 py-3 rounded shadow-lg opacity-0 pointer-events-none transition-opacity duration-300 z-50">Booking archived successfully.</div>
      <div id="successPaymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96 text-center">
          <h2 class="text-xl font-semibold mb-4 text-green-600">Success</h2>
          <p class="mb-6" id="successPaymentMessage">Payment proof submitted successfully. Awaiting verification.</p>
          <button onclick="closeSuccessPaymentModal()" class="bg-[#1d3239] text-white px-4 py-2 rounded hover:bg-[#121f28]">OK</button>
        </div>
      </div>

    <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
      <div class="bg-white rounded-xl shadow-lg p-6 max-w-md text-center">
        <h2 class="text-xl font-semibold text-green-600 mb-2">Success</h2>
        <p id="successModalMessage" class="text-gray-700 mb-4"></p>
        <button onclick="closeSuccessModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">OK</button>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <div id="rescheduleModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h2 class="text-lg font-semibold mb-4">Reschedule Appointment</h2>
        <form id="rescheduleForm">
          <input type="hidden" id="rescheduleAppointmentId">
        
          <p class="mb-3 text-sm text-gray-700">
            <strong>Employee:</strong> <span id="rescheduleEmployeeName" class="font-medium text-black"></span>
          </p>
          <label for="rescheduleDatePicker" class="block mb-2 font-medium">New Date</label>
          <input type="text" id="rescheduleDatePicker" placeholder="Select new date"class="border border-gray-300 p-2 rounded w-full mb-3">
            <div id="timePickerWrapper" class="relative hidden">
            <label for="rescheduleTimePicker" class="block mb-1 text-sm font-semibold text-[#1d3239]">Select Time</label>
            <select id="rescheduleTimePicker" name="timePicker" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1d3239]">
              <option value="">Select Time</option>
            </select>
            <p id="timeError" class="text-red-600 text-sm mt-1 hidden">Please select a time.</p>
          </div>

          <div class="flex justify-end space-x-4 mt-4">
            <button type="button" onclick="closeRescheduleModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Submit</button>
          </div>
        </form>
      </div>
    </div>
    <div id="toast" class="hidden fixed bottom-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
      Appointment rescheduled successfully.
    </div>

<script src="js/your_booking.js" defer></script>
<script>
function openMessageModal(appointmentId) {
  const row = document.querySelector(`#appointment-${appointmentId}`);
  if (!row) return;

  const employeeName = row.querySelector('td:nth-child(7)').textContent;
  const modal = document.getElementById('messageModal');
  document.getElementById('modalEmployeeName').textContent = `Message ${employeeName}`;
  modal.dataset.appointmentId = appointmentId;
  modal.classList.remove('hidden');
}

function closeMessageModal() {
  document.getElementById('messageModal').classList.add('hidden');
  document.getElementById('messageText').value = '';
}

function sendMessage() {
  const modal = document.getElementById('messageModal');
  const message = document.getElementById('messageText').value.trim();
  const appointmentId = modal.dataset.appointmentId;

  if (!message) {
    alert('Please enter a message.');
    return;
  }

  fetch('send_messages.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `appointment_id=${encodeURIComponent(appointmentId)}&message=${encodeURIComponent(message)}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Message sent successfully!');
      closeMessageModal();
    } else {
      alert('Error: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error(err);
    alert('Failed to send message.');
  });
}
  let selectedAppointmentId = null;

  function openCancelModal(appointmentId) {
    selectedAppointmentId = appointmentId;
    document.getElementById('cancelModal').classList.remove('hidden');
  }

  function closeCancelModal() {
    selectedAppointmentId = null;
    document.getElementById('cancelModal').classList.add('hidden');
  }

  function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    location.reload(); 
  }

  document.getElementById('confirmCancelBtn').addEventListener('click', () => {
    if (selectedAppointmentId !== null) {
      fetch('php/cancel_booking.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `appointment_id=${selectedAppointmentId}`
      })
      .then(response => response.json())
      .then(data => {
        closeCancelModal();
        if (data.success) {
          
          document.getElementById('successMessage').textContent = data.message;
          document.getElementById('successModal').classList.remove('hidden');
        } else {
          alert(data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert("Failed to cancel the booking.");
        closeCancelModal();
      });
    }
  });

  function showSuccessModal(message) {
  document.getElementById('successModalMessage').innerText = message;
  document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
  document.getElementById('successModal').classList.add('hidden');
  location.reload(); 
}
const dayMap = {
    "Sunday": 0, "Monday": 1, "Tuesday": 2, "Wednesday": 3,
    "Thursday": 4, "Friday": 5, "Saturday": 6
  };

  let allowedDays = [];

  document.addEventListener("DOMContentLoaded", () => {
    fetch("php/get_open_days.php")
      .then(response => response.json())
      .then(days => {
        allowedDays = days.map(day => dayMap[day]);

        flatpickr("#rescheduleDatePicker", {
          dateFormat: "Y-m-d",
          minDate: new Date().fp_incr(2),
          disable: [
            date => !allowedDays.includes(date.getDay())
          ]
        });
      });

    document.getElementById("rescheduleDatePicker").addEventListener("change", function () {
      const date = this.value;
      const appointmentId = document.getElementById("rescheduleAppointmentId").value;
      const employeeId = document.getElementById("rescheduleForm").dataset.employeeId;

      if (!date || !appointmentId || !employeeId) return;

      fetch(`php/get_time_employee.php?employee=${employeeId}&date=${date}&duration=30`)
        .then(response => response.json())
        .then(times => populateTimes(times))
        .catch(err => {
          console.error("Fetch error:", err);
          document.getElementById("rescheduleTimePicker").innerHTML = '<option value="">Error loading times</option>';
          document.getElementById("timePickerWrapper").classList.remove("hidden");
        });
    });

    function populateTimes(times) {
      const timePicker = document.getElementById('rescheduleTimePicker');
      timePicker.innerHTML = '<option value="">Select Time</option>';
      if (times.length === 0) {
        timePicker.innerHTML += '<option value="">No available time</option>';
      } else {
        times.forEach(time => {
          timePicker.innerHTML += `<option value="${time}">${time}</option>`;
        });
      }
      document.getElementById('timePickerWrapper').classList.remove('hidden');
    }

    document.getElementById("rescheduleForm").addEventListener("submit", function (e) {
      e.preventDefault();

      const date = document.getElementById("rescheduleDatePicker").value;
      const time = document.getElementById("rescheduleTimePicker").value;
      const appointmentId = document.getElementById("rescheduleAppointmentId").value;

      if (!date || !time || !appointmentId) {
        alert("Please select a valid date, time, and ensure appointment ID is set.");
        return;
      }

      fetch("php/reschedule_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          appointment_id: appointmentId,
          new_date: date,
          new_time: time
        })
      })
      .then(async res => {
        const text = await res.text(); 
        try {
          const json = JSON.parse(text);
          console.log("Parsed JSON:", json);
          if (json.success) {
            showToast("Appointment rescheduled successfully.");
            closeRescheduleModal();
            setTimeout(() => location.reload(), 1500); 
          } else {
            alert("Error: " + json.message); 
          }

        } catch (e) {
          console.error("Failed to parse JSON. Raw response:\n", text);
          alert("An error occurred while rescheduling:\n" + e.message);
        }
      });
    });
  });

  function openRescheduleModal(appointmentId, currentDate, currentTime, employeeId, employeeName) {
    document.getElementById("rescheduleAppointmentId").value = appointmentId;
    document.getElementById("rescheduleDatePicker").value = "";
    document.getElementById("rescheduleTimePicker").innerHTML = '<option value="">Select Time</option>';
    document.getElementById("timePickerWrapper").classList.add("hidden");
    document.getElementById("rescheduleModal").classList.remove("hidden");
    document.getElementById("rescheduleForm").dataset.employeeId = employeeId;
    document.getElementById("rescheduleEmployeeName").innerText = employeeName;

    setTimeout(() => {
      document.getElementById("rescheduleDatePicker").dispatchEvent(new Event('change'));
    }, 500);
  }

  function closeRescheduleModal() {
    document.getElementById("rescheduleModal").classList.add("hidden");
  }
  function showToast(message, duration = 3000) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.remove('hidden');
  toast.classList.add('show');

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.classList.add('hidden'), 500); 
  }, duration);
}
function filterBookings(status) {
      const rows = document.querySelectorAll('#bookingList tr[data-status]');
      const buttons = document.querySelectorAll('.tab-button');

      buttons.forEach(btn => btn.classList.remove('active-tab'));
      document.querySelector(`.tab-button[onclick="filterBookings('${status}')"]`).classList.add('active-tab');

      let count = 1;
      rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        const isVisible = (status === 'all' || rowStatus === status);
        row.style.display = isVisible ? 'table-row' : 'none';
        if (isVisible) {
          row.querySelector('.number-cell').textContent = count++;
        }
      });

      const hasVisible = count > 1;
      document.getElementById('noBookingsMessage').classList.toggle('hidden', hasVisible);
    }

    window.onload = () => filterBookings('all');

</script>
</body>
</html>
