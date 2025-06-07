<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon";

// Connect with port 4306 as in your original code
$conn = new mysqli('localhost:4306', $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$guide = null;

if (isset($_GET['id'])) {
    $guide_id = intval($_GET['id']); // sanitize input as integer

    // Use prepared statement for security
    $stmt = $conn->prepare("SELECT * FROM beauty_guide WHERE id = ?");
    $stmt->bind_param("i", $guide_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $guide = $result->fetch_assoc();

        if (!empty($guide['content'])) {
          // Remove literal '\r' sequences (backslash + r)
          $guide['content'] = str_replace('\r', '', $guide['content']);
          
          // Replace literal '\n' sequences (backslash + n) with actual newlines
          $guide['content'] = str_replace('\n', "\n", $guide['content']);
          
          // Convert Markdown ** to <strong> for bold text
          $guide['content'] = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $guide['content']);
      } else {
          $guide['content'] = '';
      }
      
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($guide['title'] ?? 'Guide'); ?> - Stylicle</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

    .glass-card {
      backdrop-filter: blur(12px);
      background: rgba(255, 255, 255, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.4);
    }

    .divider {
      height: 3px;
      background: linear-gradient(to right, #e91e63, #9c27b0);
      border-radius: 2px;
    }

    .guide-image {
      width: 100%;
      height: auto;
      border-radius: 8px;
      margin-bottom: 16px;
    }

    /* Adjust spacing and alignment for smaller screens */
    @media (max-width: 768px) {
      .back-button {
        margin-left: 0;
      }

      .content-container {
        padding: 1rem;
      }
    }
  </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-100">

<aside class="bg-[#1d3239] w-64 min-h-screen shadow-lg fixed top-0 left-0 px-6 py-10 space-y-6 hidden md:block z-50 text-[#f3f4f6]">
    <div class="absolute top-0 right-0 h-full w-8 bg-[#ffb199] rounded-l-full z-0"></div>
    <div class="relative z-10">
        <div class="text-center mb-10">
            <img src="../images/logo1.png" alt="Stylicle Logo" class="w-32 mx-auto" />
        </div>
        <nav class="flex flex-col space-y-4">
            <a href="landing.php" class="hover:text-[#fe7762] flex items-center transition">
                <i class="fas fa-home mr-3"></i>Home
            </a>
            <a href="services.php" class="hover:text-[#fe7762] flex items-center transition">
                <i class="fas fa-cut mr-3"></i>Services
            </a>
            <a href="your_bookings.php" class="hover:text-[#fe7762] flex items-center transition">
                <i class="fas fa-calendar-alt mr-3"></i>My Bookings
            </a>
            <a href="guide.php" class="bg-[#fe7762] text-white flex font-semibold items-center rounded-md px-2 py-1 shadow hover:bg-[#e45a4f] transition">
                <i class="fas fa-book mr-3"></i>Beauty Guide
            </a>
            <div class="border-t border-[#334155] pt-4 mt-4">
                <a href="profile.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-user mr-3"></i>Profile
                </a>
                <a href="logout.php" class="hover:text-[#fe7762] flex items-center transition">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </a>
            </div>
        </nav>
    </div>
</aside>
<!-- Back Button -->
<div class="p-6 mt-4 md:mt-4 ml-60 z-20 relative back-button"> <!-- Adjusted ml-60 -->
  <a href="guide.php" class="inline-flex items-center text-slate-600 hover:text-slate-700 text-sm font-semibold transition">
    <i class="fas fa-arrow-left mr-2"></i> Back 
  </a>
</div>

<!-- Content -->
<main class="flex-grow flex justify-center items-start px-4 ml-60 content-container"> <!-- Adjusted ml-60 -->
  <div class="glass-card max-w-3xl w-full p-8 shadow-xl rounded-xl mt-4 mb-16"> <!-- Added mb-16 -->

    <?php if ($guide): ?>
      <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($guide['title']); ?></h1>
      <p class="text-sm text-gray-600 mb-4 italic">Posted on: <?php echo htmlspecialchars($guide['created_at']); ?></p>
      <div class="divider mb-2"></div>

      <?php
      if (!empty($guide['media'])) {
          $imagePath = '../admin/uploads/' . ltrim($guide['media'], '/');
          if (file_exists($imagePath)) {
              echo "<img src='$imagePath' alt='Beauty Guide Image' class='guide-image'>";
          } else {
              echo "<img src='../images/about.jpg' alt='Default Beauty Guide Image' class='guide-image'>";
          }
      } else {
          echo "<img src='../images/about.jpg' alt='Default Beauty Guide Image' class='guide-image'>";
      }
      ?>

      <div class="text-gray-800 leading-snug  whitespace-pre-line text-[17px]">
        <?php echo nl2br($guide['content']); ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-700 text-lg">Sorry, this guide was not found.</p>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
