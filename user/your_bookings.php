<?php
include '../db.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id === 0) {
    exit("User not logged in.");
}

$limit = 10;
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
            a.appointment_date, 
            a.start_time, 
            p.payment_status, 
            a.appointment_status,
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
    <title>Stylicle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/your_booking.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="font-[Poppins]">
<aside class="bg-[#1d3239] w-64 min-h-screen shadow-lg fixed top-0 left-0 px-6 py-10 space-y-6 hidden md:block z-50 text-[#f3f4f6]">
    <div class="absolute top-0 right-0 h-full w-8 bg-[#ffb199] rounded-l-full z-0"></div>
        <div class="relative z-10">
            <div class="text-center mb-10">
            <img src="../images/logo1.png" alt="Stylicle Logo" class="w-32 mx-auto" />
            </div>
            <nav class="flex flex-col space-y-4">
                <a href="landing.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-home mr-3"></i>Home
                </a>
                <a href="services.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-cut mr-3"></i>Services
                </a>
                <a href="your_bookings.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition">
                    <i class="fas fa-calendar-alt mr-3"></i>My Bookings
                </a>
                <a href="guide.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-book mr-3"></i>Beauty Guide
                </a>

                <div class="border-t border-[#334155] pt-4 mt-4">
                    <a href="profile.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-user mr-3"></i>Profile
                    </a>
                    <a href="logout.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                </div>
            </nav>
        </div>
</aside>

<main class="md:ml-64 px-4 sm:px-8 py-8 bg-gray-50 min-h-screen">
        <h1 class="text-3xl font-semibold mb-6 text-center text-black">My Appointments</h1>
        <div class="mb-4 flex justify-end">
            <input
                type="text"
                id="searchInput"
                placeholder="Search bookings..."
                class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#1d3239] focus:border-[#1d3239] text-[#1d3239]"
                oninput="displayBookings()"
            />
        </div>

        <div class="overflow-x-visible rounded-lg shadow-md border border-gray-200">
          <table class="w-full table-auto text-sm text-left text-gray-700 bg-white">
              <thead class="text-xs uppercase bg-gray-100 text-gray-600">
                  <tr>
                      <th class="py-3 px-6">ID</th>
                      <th class="py-3 px-6">Service</th>
                      <th class="py-3 px-6">Date</th>
                      <th class="py-3 px-6">Time</th>
                      <th class="py-3 px-6">Payment</th>
                      <th class="py-3 px-6">Approval</th>
                      <th class="py-3 px-6">Employee</th>
                      <th class="py-3 px-6 text-center">Actions</th>
                  </tr>
              </thead>
              <tbody id="bookingList" class="text-sm">
                <?php if (mysqli_num_rows($result) > 0): ?>
                  <?php $number = ($page - 1) * $limit + 1; ?>
                  <?php while($row = mysqli_fetch_assoc($result)): ?>
                  <tr class="bg-gray-50">
                    <td class="py-2 px-4 border-b"><?= $number++ ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['service']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['appointment_date']) ?></td>
                    <td class="py-2 px-4 border-b"><?= date("g:i A", strtotime($row['start_time'])) ?></td>

                    <?php
                      $paymentStatus = $row['payment_status'] ?? 'No Payment';
                      $paymentColors = [
                        'Approved' => 'green',
                        'Pending' => 'yellow',
                        'Rejected' => 'red',
                        'No Payment' => 'gray'
                      ];
                      $color = $paymentColors[$paymentStatus] ?? 'gray';
                    ?>
                    <td class="py-2 px-4 border-b">
                      <span class="bg-<?= $color ?>-100 text-<?= $color ?>-700 py-1 px-3 rounded-full text-xs font-semibold">
                        <?= htmlspecialchars($paymentStatus) ?>
                      </span>
                    </td>

                    <?php
                     $status = $row['appointment_status'];
                     $displayStatus = $status;
                     $statusColor = 'gray';
                     
                     if ($status === 'Upcoming') {
                         $displayStatus = 'Accepted';
                         $statusColor = 'green';
                     } elseif ($status === 'Accepted') {
                         $statusColor = 'green';
                     } elseif ($status === 'Pending') {
                         $statusColor = 'yellow';
                     } elseif ($status === 'Cancelled') {
                         $statusColor = 'red';
                     }
                     
                    ?>
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
                      
                          <?php
                            date_default_timezone_set('Asia/Manila'); 
                            $appointmentDateTime = strtotime($row['appointment_date'] . ' ' . $row['start_time']);
                            $now = time();
                            $gracePeriod = 24 * 60 * 60;
                            $statusLower = strtolower($status);
                            $approvalCompleteStatuses = ['approved', 'completed'];

                           $disableCancel = in_array($statusLower, $approvalCompleteStatuses) || ($statusLower === 'cancelled') || (($appointmentDateTime - $now) <= $gracePeriod);
                        ?>

                        <button 
                            onclick="<?= !$disableCancel ? "openCancelModal(" . $row['appointment_id'] . ")" : "event.preventDefault()" ?>" 
                            class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 <?= $disableCancel ? 'text-gray-400 cursor-not-allowed' : 'text-red-600' ?>" 
                            <?= $disableCancel ? 'disabled' : '' ?>>
                            <i class="fas fa-times-circle mr-1"></i> Cancel
                        </button>


                            <?php
                              $paymentStatusLower = strtolower($paymentStatus);
                              $statusLower = strtolower($status);
                              $disablePayNow = in_array($paymentStatusLower, ['pending verification', 'paid']) || $statusLower === 'cancelled';
                            ?>
                            <button 
                              onclick="<?= !$disablePayNow ? "openPaymentMethodModal(" . $row['appointment_id'] . ")" : "event.preventDefault()" ?>" 
                              class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 <?= $disablePayNow ? 'text-gray-400 cursor-not-allowed' : 'text-green-600' ?>" 
                              <?= $disablePayNow ? 'disabled' : '' ?>>
                              <i class="fas fa-money-bill-wave mr-1"></i> Pay Now
                            </button>

                          <button onclick="archiveBooking(<?= htmlspecialchars($row['appointment_id']) ?>)" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 text-yellow-600">
                            <i class="fas fa-archive mr-1"></i> Archive
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
                <a href="?page=<?= $i ?>" class="px-3 py-1 rounded 
                    <?= $i == $page ? 'bg-[#fe7762] text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Next &raquo;</a>
            <?php endif; ?>
        </div>
</main>


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
    <p class="mb-2"><strong>Method:</strong> <span id="methodName"></span></p>
    <p class="mb-4 text-sm text-gray-600" id="methodDetails"></p>
    <img id="qrCodeImg" src="" alt="QR Code" class="w-48 h-48 mx-auto mb-4 object-contain border rounded">

    <label class="block text-sm font-medium mb-2">Upload Screenshot:</label>
    <input type="file" name="payment_screenshot" accept="image/*" class="w-full mb-4" />
    <div class="flex justify-between">
      <button onclick="closePaymentInfoModal()" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
      <button id="submitPaymentBtn" class="text-white px-4 py-2 rounded  bg-[#1d3239] text-white rounded hover:bg-[#121f28]" disabled>Submit Payment</button>
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
      <button id="confirmArchiveBtn" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Archive</button>
    </div>
  </div>
</div>

<div id="successToast" class="fixed bottom-5 right-5 bg-green-600 text-white px-6 py-3 rounded shadow-lg opacity-0 pointer-events-none transition-opacity duration-300 z-50">
  Booking archived successfully.
</div>


<script src="js/your_booking.js" defer>
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
</script>
</body>
</html>
