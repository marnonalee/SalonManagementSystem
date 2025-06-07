<?php
require_once '../db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM services WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $totalMinutes = intval($row['duration']);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        ?>
        <h2 class="text-lg font-semibold mb-4">Edit Service</h2>
        <form id="editServiceForm" enctype="multipart/form-data" class="space-y-6">

            <input type="hidden" name="service_id" value="<?php echo $row['service_id']; ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700">Service Name</label>
                <input type="text" name="service_name" value="<?php echo htmlspecialchars($row['service_name']); ?>" class="mt-1 block w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Min Price (₱)</label>
                <input type="number" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" class="mt-1 block w-full px-4 py-2 border rounded-md" step="0.01" min="0" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Max Price (₱)</label>
                <input type="number" name="price_max" value="<?php echo htmlspecialchars($row['price_max']); ?>" class="mt-1 block w-full px-4 py-2 border rounded-md" step="0.01" min="0" required>
            </div>

            <div class="flex space-x-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hours</label>
                    <input type="number" name="hours" value="<?php echo $hours; ?>" class="mt-1 block w-full px-4 py-2 border rounded-md" min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Minutes</label>
                    <input type="number" name="minutes" value="<?php echo $minutes; ?>" class="mt-1 block w-full px-4 py-2 border rounded-md" min="0" max="59">
                </div>
            </div>

            <div>
                <label for="specialization" class="block text-sm font-medium text-gray-700">Required Specialization</label>
                <select id="specialization" name="specialization_required" required class="mt-1 block w-full px-4 py-2 border rounded-md">
                    <option value="">Select Specialization</option>
                    <?php
                    $specialization_query = "SELECT DISTINCT specialization FROM employees WHERE specialization IS NOT NULL AND specialization != ''";
                    $specialization_result = $conn->query($specialization_query);

                    if ($specialization_result && $specialization_result->num_rows > 0) {
                        while ($spec = $specialization_result->fetch_assoc()) {
                            $specialization = htmlspecialchars($spec['specialization']);
                            $selected = (isset($row['specialization_required']) && $row['specialization_required'] === $specialization) ? 'selected' : '';
                            echo "<option value=\"$specialization\" $selected>$specialization</option>";
                        }
                    } else {
                        echo "<option value=\"\">No specializations found</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeEditModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-xs font-medium">Cancel</button>
                <button type="submit"  class="bg-blue-600 text-white px-4 py-2 rounded-md text-xs font-medium">Update</button>
            </div>
        </form>

        
        <?php
    } else {
        echo "<p>Service not found.</p>";
    }
}
?>
