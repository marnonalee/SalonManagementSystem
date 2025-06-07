<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon";

$conn = new mysqli('localhost:4306', $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT service_name, price, image_path, duration FROM services ORDER BY created_at";
$result = $conn->query($sql);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Stylicle | Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="script.js"></script>
    <script src="js/bookings.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
</head>
<body class="flex bg-gradient-to-br from-blue-50 via-white to-blue-50 font-[Poppins]">
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
                <a href="services.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition">
                    <i class="fas fa-cut mr-3"></i>Services
                </a>
                <a href="your_bookings.php" class="hover:text-[#fe7762] flex items-center transition">
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

<div class="flex-1 ml-0 md:ml-64 transition-all">
    <div class="md:hidden p-4 bg-white shadow-md flex justify-between items-center">
        <img src="../images/logo1.png" alt="Stylicle Logo" class="w-24">
        <button id="mobileMenuBtn" class="text-xl focus:outline-none"><i class="fas fa-bars"></i></button>
    </div>

    <div class="py-12 px-4 md:px-8 bg-gray-50  min-h-screen transition-all">
      <div class="max-w-7xl mx-auto">
          <h1 class="text-3xl md:text-4xl font-bold text-center text-[#1d3239] mb-2">Our Services</h1>
          <p class="text-center text-[#1d3239] opacity-80 mb-6">Enhance your beauty with our top-rated salon treatments.</p>

          <div class="mb-10 text-center"> 
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Search for a service..."
                class="w-full max-w-md mx-auto px-4 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#1d3239] focus:border-[#1d3239] text-[#1d3239] placeholder-gray-500 bg-white outline-none shadow-sm transition-all">
          </div>

          <div id="servicesContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                $sql = "SELECT * FROM services WHERE is_archived = 0 ORDER BY created_at DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0): 
                ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="service-card bg-gray-100 rounded-lg shadow hover:shadow-xl transition p-4 transform hover:scale-105 flex flex-col">
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

                            <button class="w-full text-white py-2 rounded-md bg-[#fe7762] text-white rounded hover:bg-[#e45a4f]  transition-all transform hover:scale-105 mt-auto flex items-center justify-center gap-2 bookNowBtn" data-service="<?php echo htmlspecialchars($row['service_name']); ?>">
                                <span>Book Now</span><i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-gray-500 col-span-3 text-center">No services available at the moment.</p>
                        <?php endif; ?>
                </div>
          </div>
      </div>
    </div>

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
                  <label for="serviceOption" class="block mb-1 text-sm font-semibold text-[#1d3239]">Select Service</label>
                  <select id="serviceOption" name="serviceOption" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1d3239]" onchange="handleServiceChange()">
                      <option value="">Select Option</option>
                      <?php
                      $sql_services = "SELECT service_name, duration, price, appointment_fee FROM services WHERE is_archived = 0 ORDER BY created_at";
                      $services_result = $conn->query($sql_services);
                      if ($services_result && $services_result->num_rows > 0) {
                          while ($row = $services_result->fetch_assoc()) {
                              echo '<option value="' . htmlspecialchars($row['service_name']) . '" data-duration="' . htmlspecialchars($row['duration']) . '" data-price="' . htmlspecialchars($row['price']) . '" data-fee="' . htmlspecialchars($row['appointment_fee']) . '">' . htmlspecialchars($row['service_name']) . '</option>';
                          }
                      } else {
                          echo '<option value="">No services available</option>';
                      }
                      ?>
                  </select>
                  <p id="serviceError" class="text-red-500 text-sm mt-1 hidden">Please select a service.</p>
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
          <h1 class="text-xl font-bold text-center mb-4 border-b border-dashed border-gray-300 pb-2 text-[#1d3239]">🧾 Stylicle Booking Receipt</h1>
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
                  <span>Price</span>
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
        <button class="bg-[#1d3239] text-white px-4 py-2 rounded-lg hover:bg-[#121f28] transition" onclick="proceedAfterViewing()">✅ I have paid</button>
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



    <script src="js/bookings.js" defer></script>


</body>
</html>
