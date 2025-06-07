<?php 
$conn = new mysqli('localhost:4306', 'root', '', 'salon');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (isset($_GET['id'])) {
  $guide_id = (int) $_GET['id'];
  $result = $conn->query("SELECT * FROM beauty_guide WHERE id = $guide_id");

  if ($result && $result->num_rows > 0) {
    $guide = $result->fetch_assoc();
    $cleaned = str_replace(['\\n', '\\r', '\\t'], "\n", $guide['content']);
    $cleaned = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $cleaned);
    $guide['content'] = nl2br(trim($cleaned));
  } else {
    $guide = null;
  }
} else {
  $guide = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($guide['title'] ?? 'Guide Not Found'); ?>- Adore & Beauty</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col">

<header class="fixed top-0 left-0 w-full bg-white bg-opacity-20 backdrop-blur-md shadow-md z-50 py-3">
  <div class="max-w-7xl mx-auto flex items-center px-4">
    <div class="brand-logo">
      <img src="images/logo1.png" alt="Beauty & Style Logo" class="h-10 w-10">
    </div>

    <nav class="flex space-x-6 mx-auto">
      <a href="index.php#home" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition">
        <i class="fas fa-home"></i> Home
      </a>
      <a href="index.php#services" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition">
        <i class="fas fa-concierge-bell"></i> Our Services
      </a>
      <a href="index.php#contact" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
      <a href="guide.php" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-semibold text-tale-900 bg-white scale-105 shadow-sm transition-transform duration-200">
        <i class="fas fa-book"></i> Beauty & Style Guide
      </a>
    </nav>

    <div class="flex space-x-4">
      <a href="login.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
        Login
      </a>
      <a href="sign-in.php" class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
        Signup
      </a>
    </div>
  </div>
</header>
  <!-- Back Button 
  <div class="mt-32">
    <a href="guide.php" class="inline-flex items-center text-slate-600 hover:text-purple-700 text-sm font-semibold transition">
      <i class="fas fa-arrow-left mr-2"></i> Back to Beauty & Style Guide
    </a>
  </div>
-->
 
  <main class="flex-grow flex justify-center items-start px-4 mt-20">
    <div class="glass-card max-w-3xl w-full p-8 shadow-xl rounded-xl mt-4 mb-16">

    <?php if ($guide): ?>
      <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($guide['title']); ?></h1>
      <p class="text-sm text-gray-600 mb-4 italic">Posted on: <?php echo htmlspecialchars($guide['created_at']); ?></p>
      <div class="divider mb-4"></div>

      <?php
        if (!empty($guide['media'])) {
            $imagePath = 'admin/' . $guide['media'];
            echo "<img src='" . htmlspecialchars($imagePath) . "' alt='Beauty Guide Image' class='guide-image'>";
        }
      ?>
      <div class="text-gray-800 text-[17px]">
        <?php echo $guide['content']; ?>
      </div>
      <?php else: ?>
        <h2 class="text-2xl font-bold text-red-600">Guide Not Found</h2>
        <p class="text-gray-700 mt-4">The guide you're looking for doesn't exist or was removed.</p>
      <?php endif; ?>
    </div>
  </main>

</body>
</html>
