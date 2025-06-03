<?php
use App\middlewares\Auth;

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$basePath = dirname($scriptName);
$logoutPath = $basePath . '/logout';

?>

<nav class="topbar navbar navbar-expand">
    <div class="container-fluid">
        <!-- Mobile menu button (only visible on small screens) -->
        <button class="mobile-menu-btn btn btn-light btn-sm d-lg-none">
            <i class="bi bi-list"></i>
        </button>

        <!-- Sidebar toggle button -->
        <div class="d-flex align-items-center">
            <div class="toggle-btn d-none d-lg-flex">
                <i class="bi bi-list"></i>
            </div>
            <span class="navbar-brand text-white mb-0 h1 d-none d-md-block"><?= app_config('site.title') ?></span>
        </div>

        <div class="d-flex align-items-center ms-auto">
            <!-- Dark mode toggle -->
            <label class="dark-mode-toggle">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
            </label>

            <!-- User Profile -->
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="https://randomuser.me/api/portraits/men/41.jpg" alt="Profile">
                    <div class="user-info d-none d-md-block">
                        <div class="user-name"><?= Auth::user()->first_name ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <i class="bi bi-chevron-down ms-1 text-white"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= $logoutPath ?>"><i class="bi bi-box-arrow-right me-2"></i>
                            Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>