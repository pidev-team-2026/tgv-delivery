// ===================================
// NAVBAR JAVASCRIPT
// ===================================

document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');

            // Fermer les dropdowns quand on ferme le menu mobile
            if (!navMenu.classList.contains('active')) {
                document.querySelectorAll('.dropdown-item.open').forEach(item => item.classList.remove('open'));
            }
            
            // Animation du burger
            const spans = this.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translateY(8px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translateY(-8px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.navbar-modern')) {
            navMenu.classList.remove('active');
            document.querySelectorAll('.dropdown-item.open').forEach(item => item.classList.remove('open'));
            const spans = mobileToggle.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });

    // Dropdown au clic en mode mobile
    document.querySelectorAll('.dropdown-item > .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const parent = this.closest('.dropdown-item');
            if (!parent) return;

            // En desktop: laisser le hover gérer, ne pas interférer
            if (!navMenu.classList.contains('active')) return;

            // Empêcher navigation si href="#"
            const href = this.getAttribute('href');
            if (href === '#' || href === '#!') {
                e.preventDefault();
            }

            parent.classList.toggle('open');
        });
    });

    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Active link highlighting
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link, .dropdown-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.style.color = '#2ecc71';
        }
    });

    // Panier count animation (exemple)
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        // Vous pouvez mettre à jour le compteur dynamiquement
        // Exemple: cartBadge.textContent = getCartCount();
    }
});
