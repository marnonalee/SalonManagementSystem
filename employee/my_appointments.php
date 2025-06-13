<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['employee']) || !isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete']) && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    $complete_sql = "UPDATE appointments SET appointment_status = 'Completed' WHERE appointment_id = ? AND employee_id = ?";
    $complete_stmt = $conn->prepare($complete_sql);
    $complete_stmt->bind_param("ii", $appointment_id, $employee_id);
    $complete_stmt->execute();
    $complete_stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept']) && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    $accept_sql = "UPDATE appointments SET appointment_status = 'Upcoming' WHERE appointment_id = ? AND employee_id = ?";
    $accept_stmt = $conn->prepare($accept_sql);
    $accept_stmt->bind_param("ii", $appointment_id, $employee_id);
    $accept_stmt->execute();
    $accept_stmt->close();
}

$sql = "
    SELECT 
        a.appointment_id,
        u.username AS client_name,
        s.service_name,
        a.appointment_date,
        a.start_time,
        a.end_time,
        p.payment_status,             
        a.appointment_status          
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    LEFT JOIN payments p ON a.appointment_id = p.appointment_id
    WHERE a.employee_id = ?
    ORDER BY a.appointment_date, a.start_time
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$img_stmt = $conn->prepare("SELECT profile_image FROM employees WHERE employee_id = ?");
$img_stmt->bind_param("i", $employee_id);
$img_stmt->execute();
$img_result = $img_stmt->get_result();
$empData = $img_result->fetch_assoc();

if (!$empData) {
    echo "Employee not found.";
    exit();
}

$profile_image = $empData['profile_image'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Appointments</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
    rel="stylesheet"
  />
  <style>
    body {
      font-family: "Inter", sans-serif;
    }
  </style>
</head>
<body class="bg-white text-gray-900">
  <aside
    class="w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col px-6 py-8 bg-white z-20"
  >
    <div class="flex items-center space-x-3 mb-10">
    <img src="uploads/<?= htmlspecialchars($profile_image); ?>" alt="User avatar" class="w-10 h-10 rounded-full object-cover" />
       
      <span class="text-slate-500 font-semibold text-lg"
        >Welcome, <?= htmlspecialchars($employee_name); ?></span
      >
    </div>

    <nav class="flex flex-col text-base space-y-2">
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="dashboard_emp.php"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <a class="flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-100 text-slate-600" href="my_appointments.php"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="messages.php"><i class="fas fa-comment-dots"></i><span>Messages</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="profile_emp.php"><i class="fas fa-user"></i><span>My Profile</span></a>
      <a class="flex items-center gap-3 text-gray-700 hover:text-slate-500" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
  </aside>

<main class="flex-1 ml-64 p-6 pt-24">
  <header class="fixed top-0 mb-6 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
    <h1 class="text-sm font-semibold text-gray-900">My Appointments</h1>
    <div class="flex items-center space-x-4"></div>
  </header>

  <div class="bg-white shadow rounded-lg overflow-x-auto">
    <div class="mb-4 space-x-2">
      <button onclick="filterAppointments('all')" class="btn-filter bg-slate-500 text-white px-3 py-1 rounded text-sm">All</button>
      <button onclick="filterAppointments('upcoming')" class="btn-filter px-3 py-1 rounded text-sm border border-gray-300">Upcoming</button>
      <button onclick="filterAppointments('completed')" class="btn-filter px-3 py-1 rounded text-sm border border-gray-300">Completed</button>
      <button onclick="filterAppointments('cancelled')" class="btn-filter px-3 py-1 rounded text-sm border border-gray-300">Cancelled</button>
      <button onclick="filterAppointments('pending')" class="btn-filter px-3 py-1 rounded text-sm border border-gray-300">Pending</button>
    </div>

    <table class="min-w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-3">Client</th>
          <th class="px-6 py-3">Service</th>
          <th class="px-6 py-3">Date</th>
          <th class="px-6 py-3">Time</th>
          <th class="px-6 py-3">Payment</th>
          <th class="px-6 py-3">Appointment Status</th>
          <th class="px-6 py-3">Action</th>
        </tr>
      </thead>
  
      <tbody class="divide-y divide-gray-100">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()) : ?>
            <?php 
              $statusForFilter = strtolower($row['appointment_status']) === 'accepted' ? 'upcoming' : strtolower($row['appointment_status']);
            ?>
            <tr class="hover:bg-gray-50" data-status="<?= $statusForFilter; ?>">
              <td class="px-6 py-4"><?= htmlspecialchars($row['client_name']); ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['service_name']); ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['appointment_date']); ?></td>
              <td class="px-6 py-4">
                <?= date("h:i A", strtotime($row['start_time'])) . ' - ' . date("h:i A", strtotime($row['end_time'])); ?>
              </td>
              <td class="px-6 py-4">
                <?php
                  switch ($row['payment_status']) {
                    case 'Pending':
                      echo '<span class="text-xs font-medium text-yellow-600">Pay Later</span>';
                      break;
                    case 'Completed':
                      echo '<span class="text-xs font-medium text-green-600">Paid</span>';
                      break;
                    default:
                      echo '<span class="text-xs font-medium text-red-600">' . htmlspecialchars(ucfirst($row['payment_status'])) . '</span>';
                      break;
                    }
                ?>
              </td>
              <td class="px-6 py-4">
                <?php 
                  $statusClass = match ($row['appointment_status']) {
                    'Cancelled' => 'text-red-600',
                    'Completed' => 'text-green-600',
                    default => 'text-yellow-600',
                  };
                ?>
                <?php 
                  $displayStatus = $row['appointment_status'] === 'Accepted' ? 'Upcoming' : ucfirst($row['appointment_status']);
                ?>
                <span class="text-xs font-medium <?= $statusClass; ?>">
                  <?= htmlspecialchars($displayStatus); ?>
                </span>
              </td>
              <td class="px-6 py-4">
                <?php if ($row['appointment_status'] === 'Pending') : ?>
                  <div class="flex items-center gap-2">
                    <form method="POST">
                      <input type="hidden" name="appointment_id" value="<?= $row['appointment_id']; ?>">
                      <button type="submit" name="accept" class="bg-green-500 text-white px-3 py-2 rounded text-xs font-semibold hover:bg-green-600">Accept</button>
                    </form>

                    <button class="cancel-btn bg-red-500 text-white px-3 py-2 rounded text-xs font-semibold hover:bg-red-600"
                      data-appointment-id="<?= $row['appointment_id']; ?>">
                      Cancel
                    </button>
                  </div>

                <?php elseif ($statusForFilter === 'upcoming') : ?>
                  <form method="POST">
                    <input type="hidden" name="appointment_id" value="<?= $row['appointment_id']; ?>">
                    <button type="submit" name="complete" class="bg-blue-600 text-white px-4 py-2 rounded text-xs font-semibold hover:bg-blue-700">Complete</button>
                  </form>
                <?php endif; ?>
              </td>

            </tr>
          <?php endwhile; ?>
        <?php endif; ?>

        <tr id="no-data-row" class="hidden">
          <td colspan="7" class="px-6 py-4 text-center text-gray-500">No appointments found.</td>
        </tr>
      </tbody>
    </table>
    
  </div>

  
</main>

<div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-lg">
    <h2 class="text-lg font-semibold mb-4">Confirm Cancellation</h2>
    <p class="mb-6">Are you sure you want to cancel this appointment?</p>
    <div class="flex justify-end space-x-4">
      <button id="modalCancelBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">No</button>
      
      <form id="cancelForm" method="POST" action="php/cancel_appointment.php" class="inline">
        <input type="hidden" name="appointment_id" id="appointment_id_input" value="" />
        <button type="submit" name="cancel" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Yes, Cancel</button>
      </form>
    </div>
  </div>
</div>

<script>
   const cancelModal = document.getElementById('cancelModal');
  const appointmentInput = document.getElementById('appointment_id_input');
  const modalCancelBtn = document.getElementById('modalCancelBtn');

  document.querySelectorAll('.cancel-btn').forEach(button => {
    button.addEventListener('click', () => {
      appointmentInput.value = button.dataset.appointmentId;
      cancelModal.classList.remove('hidden');
      cancelModal.classList.add('flex');
    });
  });

  modalCancelBtn.addEventListener('click', hideCancelModal);
  cancelModal.addEventListener('click', (e) => {
    if (e.target === cancelModal) hideCancelModal();
  });

  function hideCancelModal() {
    cancelModal.classList.add('hidden');
    cancelModal.classList.remove('flex');
  }

  function filterAppointments(status) {
    const rows = document.querySelectorAll('tbody tr[data-status]');
    const noDataRow = document.getElementById('no-data-row');
    let visibleCount = 0;

    rows.forEach(row => {
      const rowStatus = row.getAttribute('data-status');
      if (status === 'all' || rowStatus === status) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (visibleCount === 0) {
      noDataRow.classList.remove('hidden');
      noDataRow.querySelector('td').textContent = `No ${status.charAt(0).toUpperCase() + status.slice(1)} appointments found.`;
    } else {
      noDataRow.classList.add('hidden');
    }

    document.querySelectorAll('.btn-filter').forEach(btn => {
      btn.classList.remove('bg-slate-500', 'text-white');
      btn.classList.add('border', 'border-gray-300');
    });
    const activeBtn = document.querySelector(`.btn-filter[onclick="filterAppointments('${status}')"]`);
    if(activeBtn) {
      activeBtn.classList.add('bg-slate-500', 'text-white');
      activeBtn.classList.remove('border', 'border-gray-300');
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
  filterAppointments('upcoming');
});
const monthYear = document.getElementById('monthYear');
    const calendarDays = document.getElementById('calendarDays');
    const prevBtn = document.getElementById('prev');
    const nextBtn = document.getElementById('next');

    let currentDate = new Date();

    function renderCalendar(date) {
      const year = date.getFullYear();
      const month = date.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDay = firstDay.getDay();
      const totalDays = lastDay.getDate();

      monthYear.textContent = `${date.toLocaleString('default', { month: 'long' })} ${year}`;
      calendarDays.innerHTML = '';

      for (let i = 0; i < startDay; i++) {
        calendarDays.innerHTML += `<div></div>`;
      }

      for (let i = 1; i <= totalDays; i++) {
        calendarDays.innerHTML += `<div class="p-2 bg-white rounded hover:bg-slate-200 cursor-pointer">${i}</div>`;
      }
    }

    prevBtn.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar(currentDate);
    });

    nextBtn.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar(currentDate);
    });

    renderCalendar(currentDate);
</script>

</body>
</html>
