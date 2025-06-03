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

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9 col-12">
                <div class="card shadow-sm border-danger">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <span class="display-1 text-danger"><i class="bi bi-exclamation-triangle-fill"></i></span>
                        </div>
                        <h1 class="display-5 fw-bold mb-3 text-danger">
                            <?= htmlspecialchars($errorTitle ?? "Oops! An error occurred") ?>
                        </h1>
                        <p class="lead mb-4">
                            <?= htmlspecialchars($errorMessage ?? "Something went wrong. That's why you're seeing this page.") ?>
                        </p>
                        <a href="#" class="btn btn-outline-danger btn-lg mt-3">
                            Contact Administrator
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- bootstrap and jquery and custom js scripts -->
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/main.js"></script>
</body>

</html>