$(document).ready(function() {
    const $sidebar = $('#sidebar');
    const $toggleBtn = $('.toggle-btn');
    const $mobileMenuBtn = $('.mobile-menu-btn');
    const $mainContent = $('.main-content');
    const $darkModeToggle = $('#darkModeToggle');
    const $body = $('body');

    // Toggle sidebar on desktop
    $toggleBtn.on('click', function() {
        $sidebar.toggleClass('collapsed');
    });

    // Toggle sidebar on mobile
    $mobileMenuBtn.on('click', function() {
        $sidebar.toggleClass('show');
    });

    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        $body.addClass('dark-mode');
        $darkModeToggle.prop('checked', true);
    }

    // Dark mode toggle
    $darkModeToggle.on('change', function() {
        if ($(this).is(':checked')) {
            $body.addClass('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            $body.removeClass('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Close mobile sidebar when clicking outside
    $(document).on('click', function(event) {
        if (window.innerWidth < 992) {
            const isClickInsideSidebar = $sidebar.has(event.target).length > 0 || $sidebar.is(event.target);
            const isClickInsideMobileBtn = $mobileMenuBtn.has(event.target).length > 0 || $mobileMenuBtn.is(event.target);

            if (!isClickInsideSidebar && !isClickInsideMobileBtn && $sidebar.hasClass('show')) {
                $sidebar.removeClass('show');
            }
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').each(function() {
        new bootstrap.Tooltip(this);
    });
});