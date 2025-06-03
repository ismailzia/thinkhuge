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

    <div class="container py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="text-center">
            <h1 class="display-1 fw-bold text-primary mb-3">404</h1>
            <h2 class="mb-3">Page Not Found</h2>
            <p class="lead mb-4">
                Sorry, the page you are looking for does not exist or has been moved.
            </p>
            <a href="reports" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-left-circle me-2"></i>
                Go to dashboard
            </a>
        </div>
    </div>

    <!-- bootstrap and jquery and custom js scripts -->
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/main.js"></script>
</body>

</html>