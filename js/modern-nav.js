function initModernNav() {
    const nav = document.querySelector('nav');
    const navToggle = document.querySelector('.nav-toggle');
    const navClose = document.querySelector('.nav-close');

    // Add fade-in animation for nav items
    const navItems = document.querySelectorAll('nav ul li');
    navItems.forEach((item, index) => {
        item.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1 + 0.3}s`;
        item.style.opacity = '0';
    });

    // Toggle navigation
    navToggle.addEventListener('click', () => {
        nav.classList.add('nav-active');
        document.body.style.overflow = 'hidden';
    });

    navClose.addEventListener('click', () => {
        nav.classList.remove('nav-active');
        document.body.style.overflow = 'auto';
    });

    // Page transition effect
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            
            nav.classList.remove('nav-active');
            document.body.classList.add('page-transition');
            
            setTimeout(() => {
                window.location.href = href;
            }, 500);
        });
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initModernNav);
