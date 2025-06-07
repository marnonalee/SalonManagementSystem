<?php
session_start();

// Ensure employee is logged in
if (!isset($_SESSION['employee'])) {
    header("Location: ../login.php");  // Redirect if not logged in
    exit();
}

$employee_name = $_SESSION['employee']; // Get the logged-in employee's name

// Connect to DB
$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee_id from employee_name
$stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$result = $stmt->get_result();
$empData = $result->fetch_assoc();

if (!$empData) {
    echo "Employee not found.";
    exit();
}

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
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="#"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="my_appointments.php"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
      <a class="flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-100 text-slate-600" href="messages.php"><i class="fas fa-comment-dots"></i><span>Messages</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="profile_emp.php"><i class="fas fa-user"></i><span>My Profile</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
  </aside>

  <main class="flex-1 ml-64 p-6 pt-24"> 
  <header class="fixed top-0 mb-6 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
      <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-comment-dots"></i> Messages</h1>
      <div class="flex items-center space-x-4">
      </div>
  </header>

   
  </main>
</div>

</body>
</html>
