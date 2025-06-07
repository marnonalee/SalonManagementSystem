<?php
// admin_verify_payments.php
include '../db.php';

$conn = openDBConnection();

$result = $conn->query("SELECT id, customer_name, service_name, date, time, payment_screenshot, payment_status FROM bookings WHERE payment_status = 'Pending'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin - Verify Payments</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 p-8">
  <h1 class="text-2xl font-bold mb-6">Pending Payment Verifications</h1>
  <table class="min-w-full bg-white shadow rounded">
    <thead>
      <tr class="bg-gray-200 text-gray-700">
        <th class="p-3 text-left">Booking ID</th>
        <th class="p-3 text-left">Customer</th>
        <th class="p-3 text-left">Service</th>
        <th class="p-3 text-left">Date</th>
        <th class="p-3 text-left">Time</th>
        <th class="p-3 text-left">Screenshot</th>
        <th class="p-3 text-left">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr class="border-b border-gray-300">
        <td class="p-3"><?= htmlspecialchars($row['id']) ?></td>
        <td class="p-3"><?= htmlspecialchars($row['customer_name']) ?></td>
        <td class="p-3"><?= htmlspecialchars($row['service_name']) ?></td>
        <td class="p-3"><?= htmlspecialchars($row['date']) ?></td>
        <td class="p-3"><?= htmlspecialchars($row['time']) ?></td>
        <td class="p-3">
          <?php if ($row['payment_screenshot']): ?>
            <a href="uploads/payments/<?= htmlspecialchars($row['payment_screenshot']) ?>" target="_blank" class="text-blue-600 underline">View</a>
          <?php else: ?>
            No Screenshot
          <?php endif; ?>
        </td>
        <td class="p-3 space-x-2">
          <form action="admin_update_payment_status.php" method="POST" class="inline">
            <input type="hidden" name="booking_id" value="<?= $row['id'] ?>" />
            <input type="hidden" name="status" value="Verified" />
            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded">Verify</button>
          </form>
          <form action="admin_update_payment_status.php" method="POST" class="inline">
            <input type="hidden" name="booking_id" value="<?= $row['id'] ?>" />
            <input type="hidden" name="status" value="Rejected" />
            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded">Reject</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
