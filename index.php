<?php

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT service_name, price, image_path,  duration FROM services
 ORDER BY created_at DESC LIMIT 3"; 
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adore Beauty</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Lora:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</head>
<body>
  <header class="fixed top-0 left-0 w-full bg-white bg-opacity-20 backdrop-blur-md shadow-md z-50 py-3">
    <div class="max-w-7xl mx-auto flex items-center px-4">
      <div class="brand-logo">
        <img src="images/logo1.png" alt="Beauty & Style Logo" class="h-10 w-10">
      </div>

      <nav class="flex space-x-6 mx-auto">
        <a href="#home"  id="nav-home" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-semibold text-tale-900 bg-white scale-105 shadow-sm transition-transform duration-200">
          <i class="fas fa-home"></i> Home
        </a>
        <a href="#services"  id="nav-services" class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
          <i class="fas fa-concierge-bell"></i> Our Services
        </a>
        <a href="#contact"  id="nav-contact"  class="nav-link flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
          <i class="fas fa-envelope"></i> Contact Us
        </a>
        <a href="guide.php"  class="flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-tale-500 hover:text-tale-900 hover:bg-gray-100 transition-colors duration-300">
          <i class="fas fa-book"></i> Beauty & Style Guide
        </a>
      </nav>

      <div class="flex space-x-4">
        <a href="login.php" 
          class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
          Login
        </a>
        <a href="sign-in.php" 
          class="px-5 py-2 rounded-full border-2 border-gray-900 text-gray-900 font-semibold bg-transparent hover:bg-gray-900 hover:text-white transition duration-300 transform hover:scale-105">
          Signup
        </a>
      </div>
    </div>
  </header>
    
    <section id="home" class="hero-section ">
      <div class="text-center max-w-3xl">
        <div class="inline-flex items-center justify-center text-white text-3xl mb-4">
          <i class="fas fa-cut mr-3"></i>
          <span class="uppercase font-semibold tracking-wider">Professional Hair Salon</span>
        </div>
        <h1 class="text-6xl font-extrabold text-white mb-6 drop-shadow-lg">Style Starts Here</h1>
        <p class="text-lg text-white mb-8">Get the perfect look book your hair transformation today.</p>
        <a href="#services"
          style="background-color: #fe7762"
          class="inline-block hover:bg-teal-800 text-white font-semibold px-8 py-3 rounded-full shadow-lg transition">
          Book Now
        </a>
      </div>
    </section>

    <section id="about" class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col lg:flex-row items-center lg:items-start gap-10">
        <div class="lg:w-1/2">
          <h2 class="text-4xl font-bold text-gray-900 mb-6">About Our Salon</h2>
          <p class="text-gray-700 mb-4 leading-relaxed">
            At <strong>Adore Beauty</strong>, we specialize in expert hair services tailored to your unique style. 
            From trendy cuts to stunning color treatments, our experienced stylists are here to bring your vision to life.
          </p>
          <p class="text-gray-700 leading-relaxed">
            We pride ourselves on providing a warm, welcoming atmosphere where you can relax and feel confident. 
            Whether you're after a bold new look or just a trim, your hair is in good hands.
          </p>
        </div>
        <div class="lg:w-1/2">
          <img src="images/about.jpg" alt="Inside the Hair Salon" class="rounded-lg shadow-lg w-full object-cover max-h-96">
        </div>
      </div>
    </section>

    <section id="services" class="relative py-16 bg-gradient-to-br from-slate-50 to-gray-100 flex justify-center"> 
        <div class="w-full max-w-6xl text-center">
        <h1 class="text-6xl text-slate-600 uppercase tracking-widest">Our Services</h1>
            <p class="text-gray-700 mt-2">
            Discover a full range of hair services from precision cuts to professional styling.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mt-12 px-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="glass-card group relative">
                        <div class="overlay"></div>
                        <?php  
                              $mediaFile = !empty($row['image_path']) ? "admin/" . htmlspecialchars($row['image_path']) : '';
                              $imagePath = !empty($mediaFile) ? $mediaFile : 'images/about.jpg';
                          ?>
                          <img src="<?php echo $imagePath; ?>" alt="Service Image" class="w-full h-48 object-cover rounded-xl">

                        <div class="p-6 relative">
                            <h3 class="text-2xl font-semibold text-white drop-shadow-md mb-4">
                                <?php echo htmlspecialchars($row['service_name']); ?>
                            </h3>
                            <ul class="text-white text-opacity-90 drop-shadow-md mb-6">
                                <li class="flex items-center mb-2">
                                    <i class="far fa-clock mr-2"></i> 
                                    Duration: 
                                    <?php
                                        $duration_minutes = $row['duration'];
                                        $hours = floor($duration_minutes / 60);
                                        $minutes = $duration_minutes % 60;

                                        $formatted_duration = "";
                                        if ($hours > 0) {
                                            $formatted_duration .= $hours . " hr";
                                        }
                                        if ($minutes > 0) {
                                            if ($formatted_duration !== "") {
                                                $formatted_duration .= " ";
                                            }
                                            $formatted_duration .= $minutes . " mins";
                                        }
                                        if ($formatted_duration === "") {
                                            $formatted_duration = "No duration specified";
                                        }
                                        echo htmlspecialchars($formatted_duration);
                                    ?>
                                </li>

                                <li class="flex items-center mb-2"><i class="fas fa-tag mr-2"></i> Price: ₱<?php echo number_format($row['price'], 2); ?></li>
                                <li class="flex items-center"><i class="fas fa-check mr-2"></i> Quality Assured</li>
                            </ul>
                            <a href="login.php">
                                <button   style="background-color: #fe7762"  class="w-full text-white py-2 rounded-lg hover:bg-slate-600 transition">
                                    Book Now
                                </button>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="col-span-3 text-gray-500">No services available at the moment.</p>
            <?php endif; ?>
            </div>
            <div class="mt-8">
                <a href="login.php">
                    <button class="px-6 py-3 text-slate-500 rounded-lg hover:text-slate-600 transition">
                        See More Services
                    </button>
                </a>
            </div>
        </div>
    </section>

    <section class="bg-white intro-text py-12 px-4">
      <div class="text-center max-w-3xl mx-auto">
        <h1 class="title text-4xl font-extrabold text-gray-900 mb-4">
        Bringing Out the Best in<br>  You Every Day
        </h1>
        <p class="subtitle text-gray-600 text-lg">
        Where passion meets style for a truly beautiful transformation.
        </p>
      </div>
    </section>

    <section class="gallery px-4 py-8 bg-white">
      <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in">
        <div class="lg:col-span-2 relative group rounded-xl overflow-hidden shadow-lg">
          <img src="images/img1.png" 
            alt="Makeup application with a brush"
            class="w-full h-[500px] object-cover transform transition-transform duration-300 ease-in-out group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-30"></div>
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="text-white text-2xl font-semibold tracking-wide drop-shadow-lg">Hair Care</span>
          </div>
        </div>

        <div class="relative group rounded-xl overflow-hidden shadow-lg">
          <img src="images/img2.png" 
            alt="Styled hair with curls"
            class="w-full h-[500px] object-cover transform transition-transform duration-300 ease-in-out group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-30"></div>
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="text-white text-2xl font-semibold tracking-wide drop-shadow-lg">Hair Cut</span>
          </div>
        </div>

        <div class="relative group rounded-xl overflow-hidden shadow-lg">
          <img src="images/img3.png"
            alt="Haircut with scissors"
            class="w-full h-[250px] object-cover transform transition-transform duration-300 ease-in-out group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-30"></div>
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="text-white text-xl font-semibold tracking-wide drop-shadow-lg">Curl & Style</span>
          </div>
        </div>

        <div class="relative group rounded-xl overflow-hidden shadow-lg">
          <img src="images/img4.png" 
            alt="Hair design with intricate braids"
            class="w-full h-[250px] object-cover transform transition-transform duration-300 ease-in-out group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-30"></div>
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="text-white text-xl font-semibold tracking-wide drop-shadow-lg">Hair Treatment</span>
          </div>
        </div>

        <div class="relative group rounded-xl overflow-hidden shadow-lg">
          <img src="images/img5.png" 
            alt="Makeup touch-up with a brush"
            class="w-full h-[250px] object-cover transform transition-transform duration-300 ease-in-out group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-30"></div>
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="text-white text-xl font-semibold tracking-wide drop-shadow-lg">Scenery</span>
          </div>
        </div>
      </div>
    </section>

    <section id="contact">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-4xl mx-auto mt-12 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 mb-8 md:mb-0">
          <h1 class="text-4xl font-playfair text-gray-800 mb-4">Get In Touch</h1>
          <p class="text-gray-600 mb-6">
            Any kind of travel information, don't hesitate to contact us for immediate customer support. We love to hear from you.
          </p>
          <form id="contactForm" method="POST">
            <div class="mb-4">
              <input name="name" class="w-full p-3 border border-gray-300 rounded" placeholder="Name (with no space)" type="text" required />
            </div>
            <div class="mb-4">
              <input name="email" class="w-full p-3 border border-gray-300 rounded" placeholder="Email" type="email" required />
            </div>
            <div class="mb-4">
              <textarea name="message" class="w-full p-3 border border-gray-300 rounded h-32" placeholder="Message" required></textarea>
            </div>
            <button 
              style="background-color: #fe7762; border-radius: 10px;" 
              class="text-white px-6 py-2" 
              type="submit">
              SUBMIT NOW
            </button>

          </form>
        </div>
        <div class="md:w-1/2 flex justify-center">
          <img
            alt="Illustration"
            height="300"
            src="https://storage.googleapis.com/a1aa/image/-USCcMsTRXgdrmQT_Ks-NN1wNeWuWxYvvGrhtSJNG5s.jpg"
            width="400"
          />
        </div>
      </div>
    </section>

    <div id="successModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl text-center max-w-md w-full">
        <h2 class="text-2xl font-semibold mb-4 text-green-700">Message Sent!</h2>
        <p id="modalMessage" class="text-gray-700 mb-6">We’ll get back to you shortly.</p>
        <button onclick="closeModal()" class="bg-slate-600 text-white px-4 py-2 rounded hover:bg-slate-700">Close</button>
      </div>
    </div>
    <footer class="bg-gray-900 text-white py-6 mt-10">
      <div class="max-w-4xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <p class="text-sm">&copy; 2025 Adore & Beauty. All Rights Reserved.</p>
      </div>
    </footer>

<script>
  document.getElementById('contactForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
      const response = await fetch('send_contact.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.text();

      if (result.trim().startsWith("Thank you for contacting us")) {
        const name = formData.get('name');
        document.getElementById('modalMessage').innerText =
          `Thank you for contacting us, ${name}! We will get back to you shortly.`;
        document.getElementById('successModal').classList.remove('hidden');
        form.reset();
      } else {
        alert(result); // show server error
      }
    } catch (error) {
      alert("There was an error submitting the form.");
      console.error(error);
    }
  });

  function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
  }
  document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll("nav a.nav-link");

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          const id = entry.target.getAttribute("id");
          const navLink = document.querySelector(`#nav-${id}`);
          
          if (entry.isIntersecting) {
            navLinks.forEach((link) => link.classList.remove("bg-tale-200", "scale-105", "shadow-sm", "font-semibold"));
            if (navLink) {
              navLink.classList.add("bg-tale-200", "scale-105", "shadow-sm", "font-semibold");
            }
          }
        });
      },
      {
        threshold: 0.6,
      }
    );

    sections.forEach((section) => {
      observer.observe(section);
    });
  });

  const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll(".nav-link");

    function onScroll() {
      let currentSectionId = "";

      sections.forEach((section) => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= 100 && rect.bottom >= 100) {
          currentSectionId = section.getAttribute("id");
        }
      });

      navLinks.forEach((link) => {
        link.classList.remove("bg-white", "scale-105", "shadow-sm", "font-semibold", "text-tale-900");
        link.classList.add("text-tale-500");

        const href = link.getAttribute("href").substring(1); // remove the '#'
        if (href === currentSectionId) {
          link.classList.add("bg-white", "scale-105", "shadow-sm", "font-semibold", "text-tale-900");
          link.classList.remove("text-tale-500");
        }
      });
    }

    window.addEventListener("scroll", onScroll);

</script>
</body>
</html>
