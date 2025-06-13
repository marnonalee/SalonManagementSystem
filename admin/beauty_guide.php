<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST["title"]);
    $content = $conn->real_escape_string($_POST["content"]);
    $mediaPath = null;

    if (!empty($_FILES['media']['tmp_name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['media']['tmp_name'];
        $fileName = basename($_FILES['media']['name']);
        $uploadDir = 'uploads/';
        $targetPath = $uploadDir . uniqid() . '_' . $fileName;

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'video/mp4'];
        $fileMimeType = mime_content_type($fileTmp);
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            echo "Invalid file type. Only JPG, PNG, and MP4 files are allowed.";
            exit;
        }

        if ($_FILES['media']['size'] > 10485760) {
            echo "File size is too large. Maximum allowed size is 10MB.";
            exit;
        }

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        if (move_uploaded_file($fileTmp, $targetPath)) {
            $mediaPath = $targetPath;
        }
    }

    $stmt = $conn->prepare("INSERT INTO beauty_guide (title, content, media) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $mediaPath);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New beauty guide added successfully.";
        header("Location: beauty_guide.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Beauty Guide - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <script src="js/beauty_guide.js" defer></script>
</head>
<body class="bg-white text-gray-900">

<div class="flex min-h-screen">
<aside class="w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col px-6 py-8 bg-white z-20">
        <div class="flex items-center space-x-2 mb-4">
            <img src="img1.png" alt="User avatar" class="w-8 h-8 rounded-full object-cover" />
            <span class="text-slate-700 font-semibold text-2xl tracking-wide top-0">Welcome <?php echo htmlspecialchars($admin_username); ?></span>
        </div>

        <nav class="flex flex-col text-base space-y-2">
            <a class="sidebar-link" href="dashboard.php"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a class="sidebar-link" href="appointments.php"><i class="fas fa-calendar-alt"></i><span>Appointments</span></a>
            <a class="sidebar-link" href="employees.php"><i class="fas fa-user-tie"></i><span>Employees</span></a>
            <a class="sidebar-link" href="services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
            <a class="sidebar-link " href="user_management.php"><i class="fas fa-users-cog"></i><span>Users Management</span></a>
            <a class="sidebar-link" href="payments.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Records</span></a>
            <a class="sidebar-link" href="payments_reports.php"><i class="fas fa-file-invoice-dollar"></i><span>Payment Methods</span></a>
            <a class="sidebar-link flex items-center space-x-3 px-3 py-2 rounded-md bg-slate-200 text-slate-900" href="beauty_guide.php"><i class="fas fa-book-open"></i><span>Beauty Guide</span></a>
            <a class="sidebar-link" href="calendar_setting.php"> <i class="fas fa-calendar-alt"></i> Calendar Settings</a>
            <a class="sidebar-link" href="terms_and_agreement.php"><i class="fas fa-users-cog"></i><span>Terms & Condition</span></a>
            <a class="sidebar-link" href="service_archive.php"><i class="fas fa-archive"></i><span>Archived</span></a>
        </nav>
        <div class="flex-grow"></div> 
        <div class="border-t border-gray-300 pt-4 flex flex-col space-y-2">
            <a class="sidebar-link " href="profile.php"><i class="fas fa-user-circle"></i><span>Profile</span></a>
            <a class="sidebar-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
        </aside>
    <main class="flex-1 ml-64 p-10">
        <header class="fixed top-0 left-64 right-0 bg-white px-8 py-4 shadow z-10 flex justify-between items-center border-b border-gray-200">
            <h1 class="text-sm font-semibold text-gray-900"><i class="fas fa-book-open mr-2 text-gray-700 text-[14px]"></i>List Beauty Guides</h1>
            <input type="text" id="searchInput" placeholder="Search..." class="w-80 px-4 py-2 border rounded-md focus:outline-none" />
            <div class="flex justify-end">
                <button onclick="toggleModal(true)" class="bg-black flex justify-end text-white text-sm px-4 py-2 rounded hover:bg-gray-800">
                <i class="fas fa-plus-circle mr-2 mt-1"></i> Add Guide
                </button>
            </div>
        </header>

        <div class="mt-14">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
                <?php
                $result = $conn->query("SELECT * FROM beauty_guide ORDER BY id DESC");
                while ($row = $result->fetch_assoc()):
                    $preview = substr(strip_tags($row['content']), 0, 100);
                    if (strlen($row['content']) > 100) $preview .= "...";
                ?>
                    <div class="guide-card bg-white rounded-lg shadow-lg overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                        <div class="relative">
                            <?php if ($row['media']): ?>
                                <img src="<?php echo htmlspecialchars($row['media']); ?>" alt="Beauty Guide Media" class="w-full h-48 object-cover">
                    <?php else: ?>
                                <div class="w-full h-48 bg-gray-300 flex justify-center items-center">
                                    <span class="text-white font-semibold">No Image</span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-0 left-0 right-0 bottom-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-50"></div>
                        </div>

                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="text-lg font-semibold text-slate-600 group-hover:text-slate-500 transition-all duration-200">
                                <?php echo htmlspecialchars(str_replace(["\r", "\n", "\\r", "\\n", "&nbsp;"], "", $row['title'])); ?>
                            </h3>
                            <p class="text-gray-600 mt-2 flex-grow">
                                <?php echo htmlspecialchars(str_replace(["\r", "\n", "\\r", "\\n", "&nbsp;"], "", $preview)); ?>
                            </p>

                            <div class="flex justify-between items-center mt-4">
                                <div class="flex items-center space-x-4">
                                    <a href="javascript:void(0);" 
                                        class="text-sm text-red-500 hover:underline read-more"
                                        data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                        data-content="<?php echo htmlspecialchars(str_replace(["\r", "\n", "&nbsp;"], "", $row['content'])); ?>"
                                        data-media="<?php echo htmlspecialchars($row['media']); ?>"
                                        data-id="<?php echo $row['id']; ?>">
                                        Read More
                                    </a>

                                    <a href="javascript:void(0);" 
                                        onclick="confirmDelete(<?php echo $row['id']; ?>)"
                                        class="text-red-500 hover:text-red-700 text-sm"
                                        title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <p id="noResultsMessage" class="text-center text-gray-500 mt-4 hidden">No results found.</p>

        </div>
    </main>
</div>
<div id="beautyGuideModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] p-6 relative overflow-y-auto">

        <button id="closeModal" class="absolute top-3 right-4 text-gray-500 text-2xl hover:text-red-500">&times;</button>

        <div id="modalContent" class="space-y-4">
            <h3 class="text-2xl font-semibold text-slate-600" id="modalTitle" contenteditable="false"></h3>
            
            <img id="modalImage" class="w-full h-48 object-cover" alt="Media">
            <input type="file" id="imageInput" class="mt-2 hidden" accept="image/*">
            
            <p id="modalText" class="text-gray-600" contenteditable="false"></p>

            <div class="flex justify-end space-x-2 mt-4">
                <button id="editBtn" class="bg-yellow-400 text-white px-4 py-2 rounded hover:bg-yellow-500">Edit</button>
                <button id="saveBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 hidden">Save</button>
                <button id="cancelBtn" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancel</button>
            </div>
        </div>

    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Are you sure you want to delete this guide?</h2>
    <form id="deleteForm" method="POST" action="php/delete_guide.php">
      <input type="hidden" name="guide_id" id="deleteGuideId">
      <div class="flex justify-end space-x-4">
        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
      </div>
    </form>
  </div>
</div>


<div id="addGuideModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-xl relative">
        <button onclick="toggleModal(false)" class="absolute top-2 right-2 text-gray-500 hover:text-black">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="text-xl font-semibold mb-4 text-slate-700">Add Beauty Guide</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" required class="w-full mt-1 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-slate-400" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Content</label>
                <textarea name="content" rows="4" required class="w-full mt-1 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-slate-400"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Image/Video</label>
                <input type="file" name="media" accept="image/*,video/*" class="w-full mt-1 border border-gray-300 rounded p-2" />
            </div>
            <div class="text-right">
                <button type="submit" class="bg-slate-600 text-white px-4 py-2 rounded hover:bg-slate-700">Save</button>
            </div>
        </form>
    </div>
</div>


<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg max-w-md text-center">
    <h3 class="text-lg font-semibold mb-4 text-gray-600">Success</h3>
    <p id="successMessage" class="mb-6"></p>
    <button onclick="closeSuccessModal()" class="bg-slate-600 text-white px-4 py-2 rounded hover:bg-slate-700">OK</button>
  </div>
</div>

<div id="updateSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg max-w-md text-center">
    <h3 class="text-lg font-semibold mb-4 text-gray-700">Success</h3>
    <p id="updateSuccessMessage" class="mb-6">Beauty guide updated successfully!</p>
    <button onclick="closeUpdateSuccessModal()" class="bg-slate-600 text-white px-4 py-2 rounded hover:bg-slate-700">OK</button>
  </div>
</div>


<script>
<?php if (isset($_SESSION['success_message'])): ?>
  window.addEventListener('DOMContentLoaded', () => {
      const successModal = document.getElementById('successModal');
      const successMessage = document.getElementById('successMessage');
      successMessage.textContent = <?php echo json_encode($_SESSION['success_message']); ?>;
      successModal.classList.remove('hidden');
  });
<?php 
unset($_SESSION['success_message']);
endif; ?>
</script>



</body>
</html>
