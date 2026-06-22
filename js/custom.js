document.addEventListener('DOMContentLoaded', function () {
    
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.style.opacity = '0';
        mainContent.style.transform = 'translateY(8px)';
        requestAnimationFrame(() => {
            mainContent.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            mainContent.style.opacity = '1';
            mainContent.style.transform = 'translateY(0)';
        });
    }

    // Add subtle hover effect to cards
    document.querySelectorAll('.card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
            card.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', function () {
            card.style.transform = 'translateY(0)';

        });
    });

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(function (alert) {
        if (!alert.classList.contains('alert-danger')) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(function () {
                    alert.remove();
                }, 300);
            }, 5000);
        }
    });

    // Add active class to sidebar links on click (for smooth feel)
    document.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            document.querySelectorAll('.nav-link').forEach(function (item) {
                item.classList.remove('active', 'bg-secondary', 'rounded');
                item.classList.remove('text-white');
                item.classList.add('text-white-50');
            });

            link.classList.add('active', 'bg-secondary', 'rounded');
            link.classList.remove('text-white-50');
            link.classList.add('text-white');
        });
    });
});