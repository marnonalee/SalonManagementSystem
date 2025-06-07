<?php
include '../db.php';

if (isset($_POST['employee_id'])) {
    $employeeId = intval($_POST['employee_id']);

    $sql = "UPDATE employees SET status = 'inactive' WHERE employee_id = $employeeId";

    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
