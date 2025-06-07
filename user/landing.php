<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon";

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];

$userQuery = "SELECT user_id FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userId = $userData['user_id'];

$notifQuery = "SELECT a.appointment_date, a.start_time, s.service_name, a.appointment_status, a.cancel_reason 
               FROM appointments a
               JOIN services s ON a.service_id = s.service_id
               WHERE a.user_id = ? 
               ORDER BY a.appointment_date DESC, a.start_time DESC";
$stmt = $conn->prepare($notifQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifResult = $stmt->get_result();

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
$userId = $_SESSION['user_id']; 

$sqlCompleted = "SELECT a.appointment_id, s.service_name, emp.name AS employee_name, a.appointment_date
FROM appointments a
JOIN services s ON a.service_id = s.service_id
JOIN employees emp ON a.employee_id = emp.employee_id
WHERE a.user_id = ? 
  AND a.appointment_status = 'Completed'
  AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.appointment_id = a.appointment_id
  )
ORDER BY a.appointment_date DESC
";




$stmt = $conn->prepare($sqlCompleted);
$stmt->bind_param("i", $userId);
$stmt->execute();
$completedNotifResult = $stmt->get_result();


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Stylicle</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/mycss.css" />
  <script src="script.js" defer></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
</head>
<body class="flex bg-gradient-to-br from-blue-50 via-white to-blue-50 font-[Poppins]">

<aside class="bg-[#1d3239] w-64 min-h-screen shadow-lg fixed top-0 left-0 px-6 py-10 space-y-6 hidden md:block z-50 text-[#f3f4f6]">
  <!-- Accent strip -->
  <div class="absolute top-0 right-0 h-full w-8 bg-[#ffb199] rounded-l-full z-0"></div>

  <!-- Content -->
  <div class="relative z-10">
  <div class="text-center mb-10">
      <img src="../images/logo1.png" alt="Stylicle Logo" class="w-32 mx-auto" />
    </div>

    <nav class="flex flex-col space-y-4">
      <a href="landing.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition">
        <i class="fas fa-home mr-3"></i>Home
      </a>
      <a href="services.php" class="hover:text-[#fe7762] flex items-center transition">
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

  <div class="md:hidden fixed top-4 left-4 z-50">
    <button onclick="document.querySelector('aside').classList.toggle('hidden')" class="bg-neutral-500 text-white p-2 rounded-full shadow-lg">
      <i class="fas fa-bars"></i>
    </button>
  </div>

  <main class="flex-1 ml-0 md:ml-64 p-6">
  <div class="flex flex-col lg:flex-row gap-6">

  <div class="flex flex-col w-full lg:w-2/3 gap-6">

    <section class="bg-gradient-to-r from-[#fe7762] via-[#ffb199] to-[#ffdbc8] rounded-xl p-6 shadow-inner">
      <h1 class="text-4xl font-bold text-[#1d3239] mb-2">
        Welcome back, <?php echo htmlspecialchars($username); ?>!
      </h1>
      <p class="text-[#3b3b3b] text-lg">
        Here’s your personalized salon dashboard 💅
      </p>
    </section>

    <section>
      <h2 class="text-xl font-semibold text-[#1d3239] mb-2">Your Upcoming Appointments</h2>

      <div class="p-4 border border-[#f3c8bc] rounded-lg bg-[#fff7f4] shadow hover:shadow-md transition min-h-[230px] flex flex-col">
        <?php
          $today = date('Y-m-d');
          $apptQuery = "SELECT a.appointment_date, a.start_time, s.service_name 
                        FROM appointments a
                        JOIN services s ON a.service_id = s.service_id
                        WHERE a.user_id = ? 
                          AND a.appointment_status = 'Upcoming'
                          AND a.appointment_date >= ?
                        ORDER BY a.appointment_date ASC, a.start_time ASC
                        LIMIT 3";
          $stmt = $conn->prepare($apptQuery);
          $stmt->bind_param("is", $userId, $today);
          $stmt->execute();
          $apptResult = $stmt->get_result();

          if ($apptResult->num_rows > 0):
        ?>
        <ul class="space-y-3">
          <?php while ($appt = $apptResult->fetch_assoc()): ?>
            <li class="border border-[#ffd2c5] p-3 rounded bg-white shadow-sm">
              <strong class="text-[#fe7762]"><?php echo htmlspecialchars($appt['service_name']); ?></strong><br />
              <span class="text-gray-700">Date:</span> <?php echo date("F j, Y", strtotime($appt['appointment_date'])); ?><br />
              <span class="text-gray-700">Time:</span> <?php echo date("g:i A", strtotime($appt['start_time'])); ?>
            </li>
          <?php endwhile; ?>
        </ul>

          <a href="your_bookings.php" class="inline-block mt-auto px-4 py-2 bg-[#1d3239] text-white rounded hover:bg-[#2a4a53] transition">
            View All
          </a>

        <?php else: ?>
          <p class="text-[#555]">You have no appointments yet. 🗓</p>
          <a href="services.php?bookNowBtn=true" class="inline-block mt-auto px-4 py-2 bg-[#fe7762] text-white rounded hover:bg-[#e45a4f] transition text-center">
            Book Now
          </a>
        <?php endif; ?>
      </div>

    </section>

  </div>

  <aside class="w-full lg:w-1/3 bg-white p-4 rounded-xl shadow sticky top-6 self-start h-fit">
    <h2 class="text-xl font-semibold text-[#1d3239] mb-4">Calendar</h2>

    <div class="p-4 bg-gray-100 rounded-lg">
      <div class="flex justify-between items-center mb-4">
        <button id="prev" class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 text-gray-700">Prev</button>
        <h4 id="monthYear" class="font-semibold text-lg text-gray-800"></h4>
        <button id="next" class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 text-gray-700">Next</button>
      </div>
      <div id="calendar">
        <div id="daysOfWeek" class="grid grid-cols-7 gap-1 text-center text-gray-700 mb-2">
          <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
        </div>
        <div id="calendarDays" class="grid grid-cols-7 gap-1 text-center"></div>
      </div>
    </div>
  </aside>

</div>


      <section class="mt-6">
        <h2 class="text-xl font-semibold text-gray-700">Notifications</h2>
        <div class="mt-2 p-4 border rounded-lg bg-white shadow max-h-80 overflow-y-auto space-y-3">
          <?php if ($notifResult->num_rows > 0): ?>
            <?php while ($row = $notifResult->fetch_assoc()): ?>
              <div class="p-3 border rounded-md <?php echo ($row['appointment_status'] == 'Accepted') ? 'bg-green-50 border-green-300 text-green-700' : 'bg-red-50 border-red-300 text-red-700'; ?>">
                <?php if ($row['appointment_status'] == 'Accepted'): ?>
                  ✅ Your appointment for <strong><?php echo htmlspecialchars($row['service_name']); ?></strong> on 
                  <strong><?php echo date("F j, Y", strtotime($row['appointment_date'])); ?></strong> 
                  at <strong><?php echo date("g:i A", strtotime($row['start_time'])); ?></strong> 
                  has been <span class="font-semibold">accepted</span>.
                <?php elseif ($row['appointment_status'] == 'Cancelled'): ?>
                  ❌ Your appointment for <strong><?php echo htmlspecialchars($row['service_name']); ?></strong> on 
                  <strong><?php echo date("F j, Y", strtotime($row['appointment_date'])); ?></strong> 
                  at <strong><?php echo date("g:i A", strtotime($row['start_time'])); ?></strong> 
                  was <span class="font-semibold">cancelled</span>.
                  <?php if (!empty($row['cancel_reason'])): ?>
                    <br><span class="text-sm text-red-500 italic">Reason: <?php echo htmlspecialchars($row['cancel_reason']); ?></span>
                  <?php endif; ?>
                <?php else: ?>
                  📌 Status update for <strong><?php echo htmlspecialchars($row['service_name']); ?></strong> on 
                  <strong><?php echo date("F j, Y", strtotime($row['appointment_date'])); ?></strong>
                  at <strong><?php echo date("g:i A", strtotime($row['start_time'])); ?></strong>: 
                  <span class="font-semibold"><?php echo htmlspecialchars($row['appointment_status']); ?></span>.
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-gray-600">🎉 You're all caught up. No new notifications.</p>
          <?php endif; ?>
        </div>


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
    </div>


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
        <?php
          for ($i = 1; $i <= 5; $i++) {
            echo '<label><input type="radio" name="rating" value="'.$i.'" required /> '.$i.'⭐</label>';
          }
        ?>
      </div>
      <label class="block mb-2 font-medium" for="review">Review (optional):</label>
      <textarea name="review" id="review" rows="4" class="w-full border rounded px-3 py-2 mb-4" placeholder="Write your review..."></textarea>
      <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded">Submit</button>
      </div>
    </form>
  </div>
</div>
      
    </div>
  </main>


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



  <script>
    const monthNames = ["January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"];

    const daysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();
    const firstDayOfMonth = (month, year) => new Date(year, month, 1).getDay();

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    const monthYearElement = document.getElementById("monthYear");
    const calendarDaysElement = document.getElementById("calendarDays");

    const renderCalendar = () => {
  const days = daysInMonth(currentMonth, currentYear);
  const firstDay = firstDayOfMonth(currentMonth, currentYear);

  monthYearElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
  calendarDaysElement.innerHTML = '';

  const today = new Date();
  const isCurrentMonth = today.getMonth() === currentMonth && today.getFullYear() === currentYear;
  const todayDate = today.getDate();

  for (let i = 0; i < firstDay; i++) {
    const empty = document.createElement("div");
    calendarDaysElement.appendChild(empty);
  }

  for (let d = 1; d <= days; d++) {
    const day = document.createElement("div");
    day.textContent = d;
    day.className = "p-2 cursor-pointer hover:bg-neutral-100 rounded-lg transition";

    if (isCurrentMonth && d === todayDate) {
      day.classList.add("bg-neutral-500", "text-white", "font-semibold");
    }

    calendarDaysElement.appendChild(day);
  }
};


    document.getElementById("prev").addEventListener("click", () => {
      currentMonth = (currentMonth === 0) ? 11 : currentMonth - 1;
      if (currentMonth === 11) currentYear--;
      renderCalendar();
    });

    document.getElementById("next").addEventListener("click", () => {
      currentMonth = (currentMonth === 11) ? 0 : currentMonth + 1;
      if (currentMonth === 0) currentYear++;
      renderCalendar();
    });

    renderCalendar();
    



    function openRatingModal(appointmentId, serviceName, employeeName) {
    document.getElementById('appointment_id').value = appointmentId;
    document.getElementById('serviceInfo').textContent = "Service: " + serviceName;
    document.getElementById('employeeInfo').textContent = "Employee: " + employeeName;
    document.getElementById('ratingModal').classList.remove('hidden');
    document.getElementById('ratingModal').classList.add('flex');
  }

  function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    document.getElementById('ratingModal').classList.remove('flex');
  }
  </script>

</body>
</html>

<?php
$conn->close();
?>
