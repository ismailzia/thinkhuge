<?php
use App\core\Flash;
?>

<!-- /app/views/layout.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'App' ?></title>
    <!-- CSS using bootstrap 5 -->
    <link rel="stylesheet" href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/css/main.css">
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <!-- Top Navigation Bar -->
    <?php include __DIR__ . '/partials/navbar_view.php'; ?>


    <!-- Sidebar Menu -->
    <?php include __DIR__ . '/partials/sidebar_view.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <?php
        Flash::displayAlerts(); // Display alerts messages if any
        ?>
        <?= $content ?? '' ?>
    </div>

    <!-- bootstrap and jquery and custom js scripts -->
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/main.js"></script>
</body>

</html>