document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = link.getAttribute('href');
            if (href && !href.startsWith('#') && !link.hasAttribute('target')) {
                e.preventDefault();
                document.getElementById('page-wrapper').classList.add('fade-out');
                setTimeout(() => {
                    window.location.href = href;
                }, 500); // Match the CSS animation duration
            }
        });
    });
});
