<?php
session_start();

if (!isset($_SESSION['employee'])) {
    header("Location: ../login.php");  
    exit();
}

$employee_name = $_SESSION['employee']; 

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$result = $stmt->get_result();
$empData = $result->fetch_assoc();

if (!$empData) {
    echo "Employee not found.";
    exit();
}

$employee_id = $empData['employee_id'];

$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM ratings WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$ratingData = $result->fetch_assoc();

$avgRating = $ratingData['avg_rating'] ?? 0;
$totalRatings = $ratingData['total_ratings'] ?? 0;

$avgRatingFormatted = number_format($avgRating, 1);

$stmt = $conn->prepare("SELECT COUNT(*) as total_appointments FROM appointments WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$appData = $result->fetch_assoc();

$totalAppointments = $appData['total_appointments'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as upcoming_appointments FROM appointments WHERE employee_id = ? AND appointment_status = 'Upcoming'");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$appData = $result->fetch_assoc();

$upcomingAppointments = $appData['upcoming_appointments'] ?? 0;


$weekly_sql = "SELECT COUNT(*) AS weekly_count FROM appointments 
               WHERE employee_id = ? 
               AND YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1)";
$stmt = $conn->prepare($weekly_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$weekly_count = $row['weekly_count'];

$stmt = $conn->prepare("SELECT employee_id, profile_image FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$result = $stmt->get_result();
$empData = $result->fetch_assoc();

if (!$empData) {
    echo "Employee not found.";
    exit();
}

$employee_id = $empData['employee_id'];
$profile_image = $empData['profile_image'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
      body {
        font-family: 'Inter', sans-serif;
      }
    </style>
</head>
<body class="bg-white text-gray-900">

<div class="flex min-h-screen">

  <aside class="w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col px-6 py-8 bg-white z-20">
    <div class="flex items-center space-x-3 mb-10">
    <img src="uploads/<?= htmlspecialchars($profile_image); ?>" alt="User avatar" class="w-10 h-10 rounded-full object-cover" />
       
      <span class="text-slate-500 font-semibold text-lg">Welcome, <?= htmlspecialchars($employee_name); ?></span>

    </div>

    <nav class="flex flex-col text-base space-y-2">
      <a class="flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-100 text-slate-600" href="#"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="my_appointments.php"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="messages.php"><i class="fas fa-comment-dots"></i><span>Messages</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="profile_emp.php"><i class="fas fa-user"></i><span>My Profile</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
  </aside>

  <main class="flex-1 ml-64 p-6 pt-24"> 
  <header class="fixed top-0 mb-6 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
      <h1 class="text-sm font-semibold text-gray-900">Dashboard</h1>
      <div class="flex items-center space-x-4">
      </div>
  </header>

    <section>
      <h2 class="flex items-center text-gray-900 font-semibold text-sm mb-6 mt-2">
        <i class="fas fa-th-large mr-2 text-gray-700 text-[14px]"></i> Overview
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
      <div class="bg-indigo-100 rounded-lg p-5 flex justify-between items-center"> 
        <div>
          <p class="text-xs text-gray-700 font-semibold mb-1">Total Appointments</p>
          <p class="text-lg font-bold text-gray-900"><?php echo $totalAppointments; ?></p>
        </div>
        <button class="border border-indigo-300 rounded-md p-2 text-indigo-600 hover:bg-indigo-200">
          <i class="fas fa-calendar-alt text-[18px]"></i>
        </button>
      </div>

      <div class="bg-slate-50 rounded-lg p-5 flex justify-between items-center">
  <div>
    <p class="text-xs text-gray-900 font-semibold mb-1">This Week's Appointments</p>
    <p class="text-lg font-bold text-gray-900">
      <?php echo $weekly_count; ?> 
    </p>
  </div>
  <button class="border border-pink-300 rounded-md p-2 text-pink-600 hover:bg-pink-200">
    <i class="fas fa-calendar-week text-[18px]"></i>
  </button>
</div>


        <div class="bg-indigo-50 rounded-lg p-5 flex justify-between items-center">
          <div>
            <p class="text-xs text-gray-700 font-semibold mb-1">Upcoming Shifts</p>
            <p class="text-lg font-bold text-gray-900"><?php echo $upcomingAppointments; ?></p>
          </div>
          <button class="border border-indigo-300 rounded-md p-2 text-indigo-600 hover:bg-indigo-200">
            <i class="fas fa-clock text-[18px]"></i>
          </button>
        </div>

        <div class="bg-pink-50 rounded-lg p-5 flex justify-between items-center">
  <div>
    <p class="text-xs text-gray-900 font-semibold mb-1">Customer Ratings (<?php echo $totalRatings; ?>)</p>
    <p class="text-lg font-bold text-gray-900"><?php echo $avgRatingFormatted; ?>/5</p>
  </div>
  <button class="border border-pink-300 rounded-md p-2 text-pink-600 hover:bg-pink-200" title="Average Rating">
    <i class="fas fa-star text-[18px]"></i>
  </button>
</div>
      </div>
      <div class="overflow-x-auto rounded-lg border border-gray-100">
        <table class="w-full text-xs text-left text-gray-600">
          <thead class="bg-gray-50 text-gray-600 font-semibold">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Client</th>
              <th class="px-4 py-3">Service</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Time</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr>
              <td class="px-4 py-3">1</td>
              <td class="px-4 py-3">Charlotte</td>
              <td class="px-4 py-3">Hair Cut</td>
              <td class="px-4 py-3">May 10, 2025</td>
              <td class="px-4 py-3">1:00-2:00 PM</td>
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-yellow-100 text-yellow-600">Pending</span>
              </td>
            </tr>
            <tr>
              <td class="px-4 py-3">2</td>
              <td class="px-4 py-3">Ina Lopez</td>
              <td class="px-4 py-3">Hair Color</td>
              <td class="px-4 py-3">May 12, 2025</td>
              <td class="px-4 py-3">10:00-11:00 AM</td>
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-600">Accepted</span>
              </td>
            </tr>
            <tr>
              <td class="px-4 py-3">3</td>
              <td class="px-4 py-3">Marco Diaz</td>
              <td class="px-4 py-3">Makeup</td>
              <td class="px-4 py-3">May 15, 2025</td>
              <td class="px-4 py-3">3:00-4:00 PM</td>
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-600">Declined</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

</body>
</html>
