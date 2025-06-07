
    document.addEventListener("DOMContentLoaded", function () {
        const sections = document.querySelectorAll("section");
        const navLinks = document.querySelectorAll(".nav-links a");

        function activateNavLink() {
            let scrollPosition = window.scrollY;

            sections.forEach((section) => {
                const sectionTop = section.offsetTop - 100;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute("id");

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    navLinks.forEach((link) => {
                        link.classList.remove("active");
                    });

                    document
                        .querySelector(`.nav-links a[href="#${sectionId}"]`)
                        .classList.add("active");
                }
            });
        }

        window.addEventListener("scroll", activateNavLink);
    });
