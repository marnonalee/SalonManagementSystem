<?php
include('../db.php');

if (isset($_GET['service'])) {
    $service_name = $_GET['service'];

    $stmt = $conn->prepare("SELECT specialization_required FROM services WHERE service_name = ? AND specialization_required IS NOT NULL AND is_archived = 0 ORDER BY service_id DESC LIMIT 1");

    $stmt->bind_param("s", $service_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();

    if ($service) {
        $specialization_required = trim($service['specialization_required']);

        $stmt = $conn->prepare("SELECT name, profile_image FROM employees WHERE specialization = ?");
        $stmt->bind_param("s", $specialization_required);
        $stmt->execute();
        $employee_result = $stmt->get_result();

        if ($employee_result && $employee_result->num_rows > 0) {
            while ($emp = $employee_result->fetch_assoc()) {
                echo '
    <div class="agent-card border border-gray-300 rounded-lg p-3 text-center cursor-pointer" 
         data-name="' . htmlspecialchars($emp['name']) . '" 
         onclick="selectAgent(this)">
         
        <img src="../employee/uploads/' . htmlspecialchars($emp['profile_image']) . '" 
             alt="' . htmlspecialchars($emp['name']) . '" 
             class="w-16 h-16 mx-auto mb-2 rounded-full object-cover border">
             
        <p class="font-medium">' . htmlspecialchars($emp['name']) . '</p>
    </div>';
 }
        } else {
            echo "No employees available for this service.";
        }
    } else {
        echo "Service not found.";
    }
}
?>
