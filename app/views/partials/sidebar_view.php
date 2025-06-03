<div id="sidebar">
    <div class="sidebar-header">
        <h3 class="mb-0">Client Manager</h3>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="reports">
                <i class="bi bi-speedometer2"></i>
                <span class="nav-link-text">Reports</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="clients">
                <i class="bi bi-people"></i>
                <span class="nav-link-text">Clients</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="transactions">
                <i class="bi bi-cash-stack"></i>
                <span class="nav-link-text">Transactions</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="api_integration">
                <i class="bi bi-braces"></i>
                <span class="nav-link-text">Api</span>
            </a>
        </li>
    </ul>
</div>
<script>
    // Get all sidebar nav links
    var links = document.querySelectorAll('#sidebar .nav-link');
    // Get all non-empty segments of the path
    var segments = window.location.pathname.split('/').filter(Boolean);
    // Assume your route is always the last (or second-to-last if there's an id)
    var lastSegment = segments[segments.length - 1] || '';
    var prevSegment = segments[segments.length - 2] || '';

    links.forEach(function (link) {
        link.classList.remove('active');
        var href = link.getAttribute('href').replace(/^\/+|\/+$/g, '');

        // Activate if matches the main route (e.g., 'client') or parent segment
        if (
            href === lastSegment ||
            href === prevSegment ||
            // Special: dashboard for root or dashboard
            (href === 'dashboard' && (lastSegment === '' || lastSegment === 'dashboard'))
        ) {
            link.classList.add('active');
        }
    });
</script>