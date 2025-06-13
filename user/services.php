<?php 
session_start();

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userId = $userData['user_id'];

$stmt = $conn->prepare("SELECT a.appointment_date, a.start_time, s.service_name, a.appointment_status, a.cancel_reason 
                        FROM appointments a
                        JOIN services s ON a.service_id = s.service_id
                        WHERE a.user_id = ? 
                        ORDER BY a.appointment_date DESC, a.start_time DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifResult = $stmt->get_result();

$sql = "SELECT service_name, price, image_path, duration FROM services ORDER BY created_at";
$result = $conn->query($sql);
$totalServices = $result->num_rows; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accept_terms'])) {
    $updateQuery = $conn->prepare("UPDATE users SET has_agreed_terms = 1 WHERE user_id = ?");
    $updateQuery->bind_param("i", $userId);
    $updateQuery->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$hasAgreed = false;
$userCheck = $conn->prepare("SELECT has_agreed_terms FROM users WHERE user_id = ?");
$userCheck->bind_param("i", $userId);
$userCheck->execute();
$result = $userCheck->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $hasAgreed = (bool) $row['has_agreed_terms'];
}

$termsContent = "";
if (!$hasAgreed) {
    $termsResult = $conn->query("SELECT content FROM terms_conditions ORDER BY id DESC LIMIT 1");
    if ($termsResult && $termsResult->num_rows > 0) {
        $termsContent = $termsResult->fetch_assoc()['content'];
    }
}

$stmt = $conn->prepare("SELECT a.appointment_id, s.service_name, emp.name AS employee_name, a.appointment_date
                        FROM appointments a
                        JOIN services s ON a.service_id = s.service_id
                        JOIN employees emp ON a.employee_id = emp.employee_id
                        WHERE a.user_id = ? 
                          AND a.appointment_status = 'Completed'
                          AND NOT EXISTS (
                              SELECT 1 FROM ratings r WHERE r.appointment_id = a.appointment_id
                          )
                        ORDER BY a.appointment_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$completedNotifResult = $stmt->get_result();

$stmt = $conn->prepare("SELECT COUNT(*) as unread_count 
                        FROM appointments 
                        WHERE user_id = ? 
                          AND appointment_status IN ('Accepted', 'Cancelled', 'Upcoming')");
$stmt->bind_param("i", $userId);
$stmt->execute();
$countResult = $stmt->get_result();
$countRow = $countResult->fetch_assoc();
$notifCount = $countRow['unread_count'] ?? 0;

$stmt = $conn->prepare("SELECT appointment_date, start_time, appointment_status, cancel_reason, 
                        (SELECT service_name FROM services WHERE service_id = appointments.service_id) AS service_name 
                        FROM appointments 
                        WHERE user_id = ? 
                          AND appointment_status IN ('Accepted', 'Cancelled', 'Upcoming') 
                        ORDER BY appointment_date DESC, start_time DESC 
                        ");
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifDropdownResult = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adore & Beauty</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/mycss.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="js/bookings.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
 
<header class="fixed top-0 right-0 z-50 ml-64 p-4 bg-white shadow flex items-center justify-between w-[calc(100%-16rem)]">
  <span class="text-sm text-gray-600">Total Services: <strong><?php echo $totalServices; ?></strong></span>
  <div class="relative inline-block">
    <div class="flex items-center gap-3">
      <input list="services" id="serviceOption" class="flex-grow border border-gray-300 rounded-lg px-4 py-2" placeholder="Search for a Service" />
      <button id="bookNowBtn" class="px-4 py-2 bg-[#fe7762] hover:bg-[#e45a4f] text-white font-medium rounded-lg transition-all transform hover:scale-105 flex items-center gap-1">
        <span>Book Now</span>
      </button>
    </div>
    <datalist id="services">
      <?php
        $sql_services = "SELECT service_name, duration, price, appointment_fee FROM services WHERE is_archived = 0 ORDER BY created_at";
        $services_result = $conn->query($sql_services);
        if ($services_result && $services_result->num_rows > 0) {
            while ($row = $services_result->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['service_name']) . '" 
                        data-duration="' . htmlspecialchars($row['duration']) . '" 
                        data-price="' . htmlspecialchars($row['price']) . '" 
                        data-fee="' . htmlspecialchars($row['appointment_fee']) . '">';
            }
        }
      ?>
    </datalist>
  </div>
      <button onclick="document.getElementById('notifDropdown').classList.toggle('hidden')" class="relative ml-8 mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
        <i class="fas fa-bell text-xl"></i>
        <?php if ($notifCount > 0): ?>
          <span class="absolute -top-1 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
            <?php echo $notifCount; ?>
          </span>
        <?php endif; ?>
      </button>
      <div id="notifDropdown" class="hidden absolute right-0 w-96 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50" style="margin-top: 400px;">
      <div class="p-4 text-sm font-semibold border-b text-gray-700 ">Notifications</div>
        <div class="max-h-80 overflow-y-auto">
          <?php if ($notifDropdownResult->num_rows > 0): ?>
            <?php while ($row = $notifDropdownResult->fetch_assoc()): ?>
              <div class="p-3 border-b hover:bg-gray-100 text-sm text-gray-700">
                <?php if ($row['appointment_status'] === 'Accepted'): ?>
                  ✅ Appointment for <strong><?php echo $row['service_name']; ?></strong> on <?php echo date("M j", strtotime($row['appointment_date'])); ?> at <?php echo date("g:i A", strtotime($row['start_time'])); ?> accepted.
                <?php elseif ($row['appointment_status'] === 'Cancelled'): ?>
                  Appointment for <strong><?php echo $row['service_name']; ?></strong> on <?php echo date("M j", strtotime($row['appointment_date'])); ?> at <?php echo date("g:i A", strtotime($row['start_time'])); ?> cancelled.
                <?php elseif ($row['appointment_status'] === 'Upcoming'): ?>
                  📌 You have an upcoming appointment for <strong><?php echo $row['service_name']; ?></strong> on <?php echo date("M j", strtotime($row['appointment_date'])); ?> at <?php echo date("g:i A", strtotime($row['start_time'])); ?>.
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="p-3 text-gray-500 text-sm">No notifications.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
</header>
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
                    <a href="services.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition"><i class="fas fa-cut mr-3"></i>Services</a>
                    <a href="your_bookings.php"class="hover:text-[#fe7762] flex items-center transition"  ><i class="fas fa-calendar-alt mr-3"></i>My Bookings</a>
                    <a href="messages_page.php"class="hover:text-[#fe7762] flex items-center transition" ><i class="fas fa-user mr-3"></i>Messages</a>
                    <div class="border-t border-[#334155] pt-4 mt-4">
                        <a href="profile.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-user mr-3"></i>Profile</a>
                        <a href="logout.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a>
                    </div>
                </nav>
          </div>
    </aside>

  <div class="flex-1 ml-0 md:ml-64 transition-all mt-28">
      <div class="py-12 px-4 md:px-8  min-h-screen transition-all">
        <div class="max-w-7xl mx-auto">
            <div id="servicesContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  <?php 
                  $sql = "SELECT * FROM services WHERE is_archived = 0 ORDER BY created_at DESC";
                  $result = $conn->query($sql);
                  if ($result && $result->num_rows > 0): 
                  ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="service-card bg-gray-100 rounded-lg shadow hover:shadow-xl transition p-4 transform hover:scale-105 flex flex-col"
                            data-service-name="<?php echo htmlspecialchars(strtolower($row['service_name'])); ?>">
                        <?php
                              $imagePath = !empty($mediaFile) ? $mediaFile : '../images/about.jpg';
                          ?>
                          <img src="<?php echo "../admin/" . htmlspecialchars($imagePath); ?>"
                              alt="Service Image"
                              class="w-full h-40 object-cover rounded-md mb-4 transition-all">

                              <h3 class="service-name text-lg font-semibold text-gray-800 mb-2 transition-all">
                                  <?php echo htmlspecialchars($row['service_name']); ?>
                              </h3>
                              <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                  <li class="flex items-center">
                                      <i class="far fa-clock mr-2"></i>
                                      Duration: 
                                      <?php
                                          $d = $row['duration'];
                                          $formatted = ($d ? floor($d / 60) . " hr " . ($d % 60) . " mins" : "Not specified");
                                          echo htmlspecialchars($formatted);
                                      ?>
                                  </li>
                                  <li class="flex items-center">
                                      <i class="fas fa-tag mr-2"></i>Price: ₱<?php echo number_format($row['price'], 2); ?>
                                  </li>
                                  <li class="flex items-center">
                                      <i class="fas fa-check mr-2"></i>Quality Assured
                                  </li>
                              </ul>
                          </div>
                      <?php endwhile; ?>
                          <?php else: ?>
                              <p class="text-gray-500 col-span-3 text-center">No services available at the moment.</p>
                          <?php endif; ?>
                  </div>
            </div>
        </div>
    </div>
  </div>
  <p id="noServiceMsg" class="text-red-500 text-sm mt-1 hidden">No service found.</p>


    
  
  <section id="bookingModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="relative max-w-xl w-full max-h-[90vh] overflow-y-auto bg-white shadow-lg p-8 rounded-lg">

          <button id="closeModal" type="button" class="absolute top-4 right-4 text-[#1d3239] hover:text-gray-700 text-2xl">
              <i class="fas fa-times"></i>
          </button>

          <h1 class="text-3xl font-bold text-center mb-4 text-[#1d3239]">Book an Appointment</h1>
          <p class="text-center mb-6 text-[#1d3239] opacity-80">
              Secure your appointment with a simple down payment! Choose your service, select a professional, and pick a date that works for you. Pay a small amount upfront, and complete the remaining balance after your service.
          </p>

          <form action="" method="POST" class="space-y-4">
          <div class="relative">
            <label class="block mb-1 text-sm font-semibold text-[#1d3239]">Selected Service</label>
            <input type="text" id="selectedServiceDisplay" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
            <input type="hidden" id="serviceOption" name="serviceOption">
          </div>

              <div class="relative">
                  <label for="datePicker" class="block mb-1 text-sm font-semibold text-[#1d3239]">Select Date:</label>
                  <input type="text" id="datePicker" 
                        class="border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-[#1d3239]" placeholder="Select date" />
                  <p id="dateError" class="text-red-500 text-sm mt-1 hidden">Please select a valid date.</p>
              </div>

              <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

              <div id="agentSelection" class="flex flex-wrap gap-4 hidden">
                  <p class="font-bold mb-2 text-[#1d3239]">Select Employee</p>
                  <div class="grid grid-cols-3 gap-4">
                      <?php
                      $sql_employees = "SELECT name, profile_image FROM employees ORDER BY name";
                      $employee_result = $conn->query($sql_employees);
                      if ($employee_result && $employee_result->num_rows > 0) {
                          while ($emp = $employee_result->fetch_assoc()) {
                              $empName = htmlspecialchars($emp['name']);
                              $empImage = htmlspecialchars($emp['profile_image']);
                              echo '
                                <div class="border border-gray-300 rounded-lg p-3 text-center agent-card cursor-pointer hover:border-[#1d3239]" data-name="' . $empName . '" onclick="selectAgent(this)">
                                  <img src="../employee/uploads/' . $empImage . '" alt="' . $empName . '" class="w-16 h-16 mx-auto mb-2 rounded-full object-cover border border-gray-300">
                                  <p class="text-[#1d3239]">' . $empName . '</p>
                              </div>';
                          }
                      }
                      ?>
                  </div>
              </div>

              <div id="timePickerWrapper" class="relative hidden">
                  <label for="timePicker" class="block mb-1 text-sm font-semibold text-[#1d3239]">Select Time</label>
                  <select id="timePicker" name="timePicker" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1d3239]">
                      <option value="">Select Time</option>
                  </select>
                  <p id="timeError" class="text-red-600 text-sm mt-1 hidden">Please select a time.</p>
              </div>

              <button id="continueButton" type="button" onclick="validateForm()" 
                      class="w-full bg-[#1d3239] text-white rounded hover:bg-[#121f28] py-2 rounded-lg transition-all">
                  CONTINUE
              </button>
          </form>
      </div>
  </section>


  <section id="summaryModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
      <div class="relative w-full max-w-md bg-white shadow-lg p-6 rounded-lg font-mono border border-gray-300">
          <button id="closeSummaryModal" type="button" class="absolute top-4 right-4 text-[#1d3239] hover:text-gray-700 text-2xl">
              <i class="fas fa-times"></i>
          </button>
          <h1 class="text-xl font-bold text-center mb-4 border-b border-dashed border-gray-300 pb-2 text-[#1d3239]">🧾 Adore & Beauty</h1>
          <div id="summaryContent" class="text-sm leading-6 space-y-2 text-[#1d3239]">
              <div class="flex justify-between">
                  <span>Service</span>
                  <span id="summaryService"></span>
              </div>
              <div class="flex justify-between">
                  <span>Date</span>
                  <span id="summaryDate"></span>
              </div>
              <div class="flex justify-between">
                  <span>Time</span>
                  <span id="summaryTime"></span>
              </div>
              <div class="flex justify-between">
                  <span>Agent</span>
                  <span id="summaryAgent"></span>
              </div>
              <div class="flex justify-between border-t border-dashed border-gray-300 pt-2">
                  <span>Price Range</span>
                  <span>₱<span id="summaryPrice"></span></span>
              </div>
              <div class="flex justify-between">
                  <span>Appointment Fee</span>
                  <span>₱<span id="summaryFee"></span></span>
              </div>
          </div>
          <div class="mt-6 border-t border-dashed border-gray-300 pt-4 space-y-2">
              <button id="payNowButton" class="w-full bg-[#1d3239] hover:bg-[#121f28] text-white py-2 rounded-lg transition" onclick="payNow()">💳 Pay Now</button>
              <button id="payLaterButton" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg transition" onclick="payLater()">🕒 Pay Later</button>
              <button id="changeOptionButton" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition" onclick="changeBookingOption()">🔄 Change Option</button>
          </div>
      </div>
  </section>

  <section id="reminderModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full relative font-mono border border-gray-300">
      <button id="closeReminderModal" class="absolute top-3 right-3 text-[#1d3239] hover:text-[#121f28] text-xl transition">
        <i class="fas fa-times"></i>
      </button>
      <h2 class="text-lg font-bold mb-2 text-center text-[#1d3239]">⏰ Payment Reminder</h2>
      <p class="text-sm text-gray-700 text-center mb-4">
        Please note that your appointment will be <strong>automatically canceled</strong> if not paid within <strong>24 hours</strong>.
      </p>
      <div class="text-center">
        <button id="okReminderButton" class="bg-[#1d3239] text-white px-4 py-2 rounded-lg hover:bg-[#121f28] transition">
          OK
        </button>
      </div>
    </div>
  </section>

  <section id="paymentMethodsModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
    <div class="relative w-full max-w-md bg-white shadow-lg p-6 rounded-lg font-mono border border-gray-300">
      <button id="closePaymentMethodsModal" type="button" class="absolute top-4 right-4 text-[#1d3239] hover:text-[#121f28] text-2xl transition">&times;</button>
      <h2 class="text-xl font-bold text-center mb-4 border-b border-dashed pb-2 text-[#1d3239]">Choose Payment Method</h2>
      <div id="paymentMethodsList" class="space-y-4 max-h-72 overflow-y-auto text-[#1d3239]" >
      </div>
      <button id="confirmPaymentMethodBtn" class="mt-6 w-full bg-[#1d3239] text-white py-2 rounded-lg disabled:opacity-50" disabled>
        Confirm Payment Method
      </button>
    </div>
  </section>

  <section id="paymentDetailsModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
    <div class="relative w-full max-w-md bg-white shadow-lg p-6 rounded-lg font-mono border border-gray-300">
      <button id="closePaymentDetailsModal" type="button" class="absolute top-4 right-4 text-[#1d3239] hover:text-[#121f28] text-2xl transition">&times;</button>
      <h2 class="text-xl font-bold text-center mb-4 border-b border-dashed pb-2 text-[#1d3239]">Payment Instructions</h2>

      <div id="paymentDetailsContent" class="text-sm space-y-4 text-[#1d3239]">
        <div id="paymentMethodName" class="text-lg font-semibold text-center"></div>

        <div id="paymentMethodContact" class="text-center text-gray-700 mt-2 font-semibold"></div>
        <div class="flex flex-col items-center">
          <p class="text-sm font-semibold text-gray-700 mb-2">📷 Scan this QR code</p>
          <img id="paymentMethodQR" src="" alt="QR Code" class="w-40 h-40 object-contain border rounded" />
        </div>

        <div id="paymentMethodDetails" class="text-center text-gray-700"></div>
          <div id="paymentMethodContact_Number" class="text-center text-gray-700 mt-2 font-semibold"></div>

        <div class="mt-4">
          <label for="paymentProof" class="block text-center font-semibold mb-1">Upload Screenshot as Proof of Payment:</label>
          <input type="file" id="paymentProof" name="paymentProof" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required />
        </div>
      </div>

      <div class="mt-6 text-center">
        <button class="bg-[#1d3239] text-white px-4 py-2 rounded-lg hover:bg-[#121f28] transition" onclick="proceedAfterViewing()">Submit</button>
      </div>
    </div>
  </section>
  <section id="confirmCancelModal" class="hidden fixed inset-0 bg-[#1d3239cc] flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center font-mono border border-gray-300">
      <h2 class="text-xl font-semibold mb-4 text-[#1d3239]">Cancel Booking</h2>
      <p class="mb-6 text-[#1d3239]">Are you sure you want to cancel your booking?</p>
      <div class="flex justify-center gap-4">
        <button id="confirmYes" class="bg-[#1d3239] text-white px-4 py-2 rounded hover:bg-[#121f28] transition">
          Yes
        </button>
        <button id="confirmNo" class="bg-gray-300 text-[#1d3239] px-4 py-2 rounded hover:bg-gray-400 transition">
          No
        </button>
      </div>
    </div>
  </section>

<div id="successModal" class="fixed inset-0 bg-[#1d3239cc] flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg p-6 w-80 text-center shadow-lg font-mono border border-gray-300">
    <h2 class="text-xl font-semibold text-[#1d3239] mb-4">Success</h2>
    <p class="text-[#1d3239]">Payment proof submitted. Your appointment is pending verification.</p>
    <button onclick="closeSuccessModal()" class="mt-4 bg-[#1d3239] text-white px-4 py-2 rounded hover:bg-[#121f28] transition">
      OK
    </button>
  </div>
</div>

<div id="appointmentSuccessModal" class="fixed inset-0 bg-[#1d3239cc] flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg p-6 w-80 text-center shadow-lg font-mono border border-gray-300">
    <h2 class="text-xl font-semibold text-[#1d3239] mb-4">Success</h2>
    <p class="text-[#1d3239]">Appointment booked successfully! Please pay within 24 hours.</p>
    <button onclick="closeAppointmentSuccessModal()" class="mt-4 bg-[#1d3239] text-white px-4 py-2 rounded hover:bg-[#121f28] transition">
      OK
    </button>
  </div>
</div>



<main>
  <section class="mt-6">
    <?php if ($completedNotifResult->num_rows > 0): ?>
      <h3 class="mt-4 font-semibold text-gray-700">Rate Completed Appointments</h3>
      <?php while ($row = $completedNotifResult->fetch_assoc()): ?>
        <div 
          class="cursor-pointer p-3 border rounded-md bg-yellow-50 border-yellow-300 text-yellow-700 hover:bg-yellow-100"
          onclick="openRatingModal(<?php echo $row['appointment_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['service_name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['employee_name'])); ?>')"
        >
          ⭐ Please rate your experience with <strong><?php echo htmlspecialchars($row['employee_name']); ?></strong> for <strong><?php echo htmlspecialchars($row['service_name']); ?></strong> on <?php echo date("F j, Y", strtotime($row['appointment_date'])); ?>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </section>
</main>
<div id="ratingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-lg relative">
    <button onclick="closeRatingModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
      <i class="fas fa-times"></i>
    </button>
    <h2 class="text-xl font-semibold mb-4">Rate Your Appointment</h2>
    <form id="ratingForm" method="POST" action="php/submit_rating.php">
      <input type="hidden" name="appointment_id" id="appointment_id" value="" />
      <p class="mb-2" id="serviceInfo"></p>
      <p class="mb-4" id="employeeInfo"></p>
      <label class="block mb-2 font-medium">Rating:</label>
      <div class="flex space-x-2 mb-4">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <label><input type="radio" name="rating" value="<?php echo $i; ?>" required /> <?php echo $i; ?>⭐</label>
        <?php endfor; ?>
      </div>
      <label class="block mb-2 font-medium" for="review">Review (optional):</label>
      <textarea name="review" id="review" rows="4" class="w-full border rounded px-3 py-2 mb-4" placeholder="Write your review..."></textarea>
      <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded">Submit</button>
      </div>
    </form>
  </div>
</div>

<?php if (!$hasAgreed): ?>
  <div id="termsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-xl max-h-[80vh] flex flex-col">
      <h2 class="text-xl font-semibold mb-4 text-gray-800">Terms and Conditions</h2>
      <div class="border border-gray-300 p-4 rounded overflow-y-auto text-gray-700 text-sm flex-grow mb-4" style="min-height: 300px;">
        <?php echo nl2br(htmlspecialchars($termsContent)); ?>
      </div>
      <form method="POST" class="flex justify-end">
        <button type="submit" name="accept_terms" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 px-6 rounded">
          I Agree
        </button>
      </form>
    </div>
  </div>
<?php endif; ?>

<div id="toast" class="fixed top-24 right-5 bg-red-500 text-white px-4 py-2 rounded shadow-lg hidden z-50 transition-all duration-300">
  No service found. Please select a valid service from the list.
</div>

<script>
  function openRatingModal(id, service, employee) {
    document.getElementById('appointment_id').value = id;
    document.getElementById('serviceInfo').textContent = "Service: " + service;
    document.getElementById('employeeInfo').textContent = "Employee: " + employee;
    document.getElementById('ratingModal').classList.remove('hidden');
    document.getElementById('ratingModal').classList.add('flex');
  }

  function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    document.getElementById('ratingModal').classList.remove('flex');
  }
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('serviceOption');
    const serviceCards = document.querySelectorAll('.service-card');

    searchInput.addEventListener('input', function () {
      const query = this.value.toLowerCase().trim();

      serviceCards.forEach(card => {
        const name = card.getAttribute('data-service-name');
        if (name.includes(query) || query === "") {
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });
    });

    bookNowBtn.addEventListener('click', function () {
      const serviceInput = document.getElementById('serviceOption');
      const serviceName = serviceInput.value.trim();

      if (!serviceName) {
        showToast("Please type or select a service first.");
        return;
      }

      const serviceOptions = document.querySelectorAll('#services option');
      let isValidService = false;

      serviceOptions.forEach(option => {
        if (option.value.toLowerCase() === serviceName.toLowerCase()) {
          isValidService = true;
        }
      });

      if (!isValidService) {
        showToast("No service found. Please select a valid service from the list.");
        return;
      }

      document.getElementById('selectedServiceDisplay').value = serviceName;
      document.getElementById('bookingModal').classList.remove('hidden');
    });

    const closeModalBtn = document.getElementById('closeModal');
    if (closeModalBtn) {
      closeModalBtn.addEventListener('click', function () {
        document.getElementById('bookingModal').classList.add('hidden');
      
      });
    }
  });
  function showToast(message, duration = 3000) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('opacity-100');

    setTimeout(() => {
      toast.classList.add('hidden');
    }, duration);
  }
  const serviceData = {
    <?php
    mysqli_data_seek($services_result, 0); 
    while ($row = $services_result->fetch_assoc()) {
        $name = addslashes($row['service_name']);
        echo "'$name': {
          duration: " . (int)$row['duration'] . ",
          price: " . (float)$row['price'] . ",
          fee: " . (float)$row['appointment_fee'] . "
        },";
    }
    ?>
  };


let activityTimer;

function sendActivityUpdate() {
  fetch("php/update_activity.php", { method: "POST" });
}

function resetTimer() {
  clearTimeout(activityTimer);
  activityTimer = setTimeout(() => {
    sendActivityUpdate(); 
  }, 3000); 
}

['mousemove', 'keydown', 'scroll', 'click'].forEach(event => {
  document.addEventListener(event, resetTimer);
});

resetTimer(); 

</script>

</body>
</html>

<?php $conn->close(); ?>
