<?php
$conn = new mysqli('localhost:4306', 'root', '', 'salon');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adore & Beauty - Beauty & Style Guide</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
  <script src="script.js"></script>
</head>

<body class="bg-gray-100">

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

<div class="text-center mt-24">
  <h1 class="text-3xl font-bold text-white">Beauty & Style Guide</h1>
</div>

<div class="container mx-auto mt-12 px-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
      $result = $conn->query("SELECT * FROM beauty_guide ORDER BY id DESC");
      while ($row = $result->fetch_assoc()):
          $mediaFile = $row['media'];
          $imagePath = (!empty($mediaFile)) ? 'admin/' . $mediaFile : 'images/about.jpg';

          $raw_content = strip_tags($row['content']);
          $cleaned_content = preg_replace('/\s+/', ' ', str_replace('\\n', ' ', $raw_content));
          $short_content = substr($cleaned_content, 0, 100);
    ?>
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Beauty Guide Image" class="w-full h-48 object-cover rounded-lg">
        <h2 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($row['title']); ?></h2>
        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($short_content); ?>...</p>
        <div class="flex justify-between items-center mt-4">
          <a class="text-red-500 text-sm font-bold" href="guide_post.php?id=<?php echo $row['id']; ?>">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

</body>
</html>
