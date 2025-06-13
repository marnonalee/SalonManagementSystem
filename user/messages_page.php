<?php 
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['user'];
$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
if (!$userData) {
    echo "User not found.";
    exit();
}
$user_id = $userData['user_id'];

$stmt = $conn->prepare("
    SELECT DISTINCT e.employee_id, e.name, e.profile_image
    FROM messages m
    JOIN appointments a ON m.appointment_id = a.appointment_id
    JOIN employees e ON a.employee_id = e.employee_id
    WHERE a.user_id = ?
");

$unreadStmt = $conn->prepare("
    SELECT COUNT(*) AS unread_count 
    FROM messages m
    JOIN appointments a ON m.appointment_id = a.appointment_id
    WHERE a.user_id = ? AND m.sender_role = 'employee' AND m.is_read = 0
");
$unreadStmt->bind_param("i", $user_id);
$unreadStmt->execute();
$unreadResult = $unreadStmt->get_result();
$unreadData = $unreadResult->fetch_assoc();
$unreadCount = $unreadData['unread_count'];

$stmt->bind_param("i", $user_id);
$stmt->execute();
$employeesResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Adore & Beauty - Message</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap"/>
    <style>
      #modalContent::-webkit-scrollbar { width: 6px; }
      #modalContent::-webkit-scrollbar-thumb { background-color: #cbd5e0; border-radius: 6px; }
    </style>
</head>
<body class="bg-neutral-100">
<header class="fixed top-0 left-64 right-0 z-50 p-4 bg-white shadow flex justify-start pl-6">
    <h2 class="text-2xl font-semibold">Messages</h2>
    
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
            <a href="services.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-cut mr-3"></i>Services</a>
            <a href="your_bookings.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-calendar-alt mr-3"></i>My Bookings</a>
            <a href="messages_page.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition"><i class="fas fa-user mr-3"></i>Messages</a>
            <div class="border-t border-[#334155] pt-4 mt-4">
                <a href="profile.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-user mr-3"></i>Profile</a>
                <a href="logout.php" class="hover:text-[#fe7762] flex items-center transition"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a>
            </div>
        </nav>
    </div>
</aside>

<section class="ml-72 mt-24 p-6">
    <div class="mb-4">
        <input 
            type="text" 
            id="searchInput" 
            placeholder="Search employee..." 
            class="w-full px-4 py-2 border rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
    </div>

    <?php if ($employeesResult->num_rows > 0): ?>
        <div class="bg-white rounded shadow divide-y" id="employeeList">
            <?php while ($emp = $employeesResult->fetch_assoc()): ?>
                <?php
                $uploadDir = __DIR__ . '../employee/uploads/';
                if (!empty($emp['profile_image']) && file_exists($uploadDir . $emp['profile_image'])) {
                    $imgPath = '../employee/uploads/' . $emp['profile_image'];
                } else {
                    $imgPath = '../employee/uploads/default.jpg';
                }
                ?>
                <div class="employee-item" data-name="<?= strtolower($emp['name']) ?>">
                    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-4">
                            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($emp['name']) ?>" class="w-12 h-12 rounded-full object-cover" />
                            <div>
                                <h3 class="text-base font-medium"><?= htmlspecialchars($emp['name']); ?></h3>
                            </div>
                        </div>
                        <button onclick="openModal(<?= $emp['employee_id'] ?>)" class="text-blue-600 hover:underline text-sm">View</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600">You have no messages with any employees yet.</p>
    <?php endif; ?>
</section>

<div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white w-11/12 max-w-lg p-6 rounded shadow-lg relative flex flex-col max-h-[90vh]">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-xl">&times;</button>
        <h2 class="text-xl font-semibold mb-4">Messages</h2>
        <div id="modalContent" class="space-y-2 overflow-y-auto flex-1 mb-4 px-2"></div>
        <div class="flex space-x-2">
            <input type="text" id="messageInput" placeholder="Type your message..." class="flex-1 border px-3 py-2 rounded" />
            <button onclick="sendMessage()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Send</button>
        </div>
    </div>
</div>

<script src="js/messages.js" defer></script>
<script>
document.getElementById('searchInput').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    document.querySelectorAll('.employee-item').forEach(item => {
        const name = item.getAttribute('data-name');
        if (name.includes(searchValue)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
