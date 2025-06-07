<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon";

$conn = new mysqli('localhost:4306', $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Stylicle - Beauty & Style Guide</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .guide-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .guide-card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
        }
        .guide-card img {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .guide-card img:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        .read-more-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .read-more-btn::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: rgb(236, 72, 72);
            bottom: 0;
            left: 50%;
            transition: width 0.3s ease, left 0.3s ease;
        }
        .read-more-btn:hover {
            color: rgb(236, 72, 72);
        }
        .read-more-btn:hover::after {
            width: 100%;
            left: 0;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-slate-100 to-purple-100 flex">

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
<div class="ml-64 w-full py-12 px-4 md:px-8 bg-gray-50 min-h-screen">
    <div class="text-center mt-10">
        <h1 class="text-4xl font-bold text-gray-800">Beauty & Style Guide</h1>
    </div>

    <div class="flex justify-center mt-8">
        <div class="relative w-full max-w-md">
            <input type="text" id="search" placeholder="Search guides..." class="w-full py-3 px-6 rounded-lg shadow-md border focus:outline-none focus:ring-1 focus:ring-[#1d3239] focus:border-[#1d3239] text-[#1d3239] transition ease-in-out duration-200" />
            <div class="absolute top-0 right-0 mt-3 mr-4">
                <button class="text-gray-600">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto mt-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php
            $result = $conn->query("SELECT * FROM beauty_guide ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $content = $row['content'];

                $content = str_replace(['&nbsp;', '<div>', '</div>'], '', $content);
                $content = str_replace('\n', ' ', $content);
                $short_content = substr($content, 0, 100);
                $mediaFile = $row['media'];
                $imagePath = !empty($mediaFile) ? '../admin/uploads/' . $mediaFile : '../images/about.jpg';
            ?>

            <div class="bg-white p-6 rounded-lg shadow-xl transform hover:scale-105 transition-all duration-300 ease-in-out hover:shadow-2xl flex flex-col guide-card" data-title="<?php echo strtolower($row['title']); ?>">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Beauty Guide Image" class="w-full h-48 object-cover rounded-lg transition-all duration-300 ease-in-out hover:opacity-90" />
                <h2 class="text-xl font-bold mt-4 text-gray-800 transition-colors duration-300 ease-in-out hover:text-slate-700"><?php echo htmlspecialchars($row['title']); ?></h2>
                <p class="text-gray-600 mt-2 flex-grow"><?php echo htmlspecialchars($short_content); ?>...</p>
                <div class="mt-auto">
                    <a class="text-red-500 text-sm font-bold hover:text-red-600 transition-colors duration-200 read-more-btn" href="guide_post.php?id=<?php echo $row['id']; ?>">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Live Search Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search");
    const cards = document.querySelectorAll(".guide-card");

    searchInput.addEventListener("input", function () {
        const searchText = searchInput.value.toLowerCase();

        cards.forEach(card => {
            const title = card.getAttribute("data-title");
            if (title.includes(searchText)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });
    });
});
</script>

</body>
</html>
