<?php
session_start();

// Ensure employee is logged in
if (!isset($_SESSION['employee'])) {
    header("Location: ../login.php");  // Redirect if not logged in
    exit();
}

$employee_name = $_SESSION['employee']; // Logged-in employee's name

// Connect to DB
$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee_id and profile_image from employee name
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

// Fetch messages sent to this employee via appointments
$stmt = $conn->prepare("
    SELECT DISTINCT u.user_id, u.username
    FROM messages m
    INNER JOIN appointments a ON m.appointment_id = a.appointment_id
    INNER JOIN users u ON a.user_id = u.user_id
    WHERE a.employee_id = ?
");


$stmt->bind_param("i", $employee_id);
$stmt->execute();
$messagesResult = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee Dashboard - Messages</title>
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

  <!-- Sidebar -->
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

  <!-- Main content -->
  <main class="flex-1 ml-64 p-6 pt-24">
    <header class="fixed top-0 mb-6 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
      <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-comment-dots"></i> Messages</h1>
      <div class="flex items-center space-x-4"></div>
    </header>
    <section class="space-y-4">
  <?php if ($messagesResult->num_rows > 0): ?>
    <?php while ($row = $messagesResult->fetch_assoc()): ?>
      <div class="border border-gray-200 p-4 rounded shadow-sm flex justify-between items-center">
        <p class="text-gray-800"><strong><?= htmlspecialchars($row['username']); ?></strong></p>
        <button onclick="openModal(<?= $row['user_id'] ?>)" class="text-blue-600 hover:underline text-sm">View Conversation</button>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-gray-600">No messages yet.</p>
  <?php endif; ?>
</section>


  </main>
  <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white w-11/12 max-w-lg p-6 rounded shadow-lg relative flex flex-col max-h-[90vh]">
    <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-xl">&times;</button>
    <h2 class="text-xl font-semibold mb-4">Conversation</h2>
    <div id="modalContent" class="space-y-2 overflow-y-auto flex-1 mb-4 max-h-[50vh]"></div>
    <div class="flex space-x-2">
      <input type="text" id="messageInput" placeholder="Type your message..." class="flex-1 border px-3 py-2 rounded" />
      <button onclick="sendMessage()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Send</button>
    </div>
  </div>
</div>

</div>
<script>
let currentUserId = null;

function openModal(userId) {
  currentUserId = userId;
  loadMessages(userId);
  document.getElementById('messageModal').classList.remove('hidden');
  document.getElementById('messageModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('messageModal').classList.add('hidden');
  document.getElementById('messageModal').classList.remove('flex');
  currentUserId = null;
}

function loadMessages(userId) {
  fetch(`php/get_messages_employee.php?user_id=${userId}`)
    .then(response => response.json())
    .then(data => {
      const modalContent = document.getElementById('modalContent');
      modalContent.innerHTML = '';
      if (data.length === 0) {
        modalContent.innerHTML = '<p class="text-gray-600">No messages found.</p>';
      } else {
        data.forEach(msg => {
          const sender = msg.sender_role === 'employee' ? 'You' : msg.sender_name;
          const msgElement = `
            <div class="border p-2 rounded bg-gray-100">
              <p><strong>${sender}:</strong> ${msg.message}</p>
              <p class="text-xs text-gray-500">${msg.sent_at}</p>
            </div>`;
          modalContent.innerHTML += msgElement;
        });
        modalContent.scrollTop = modalContent.scrollHeight;
      }
    });
}

function sendMessage() {
  const input = document.getElementById('messageInput');
  const message = input.value.trim();
  if (!message || !currentUserId) return;

  fetch('php/send_message_to_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: currentUserId, message })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      input.value = '';
      loadMessages(currentUserId);
    } else {
      alert(data.error || 'Failed to send message.');
    }
  });
}
</script>

</body>
</html>
