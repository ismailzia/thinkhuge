<?php
use App\core\Flash;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - App</title>
    <link rel="stylesheet" href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/css/main.css">
    <link rel="stylesheet" href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/css/auth.css">

</head>

<body>
    <div class="auth-container">
        <div class="auth-card card">
            <div class="card-body p-4 p-md-5">

                <?php
                Flash::displayAlerts();
                ?>
                <div class="auth-header">
                    <h2 class="fw-bold mb-3">Welcome Back</h2>
                    <p class="text-muted">Sign in to continue to your account</p>
                </div>

                <form id="loginForm" action="auth/login" method="post">
                    <!-- Email -->

                    <?= csrf_field() ?>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
                            required>
                        <label for="email">Email Address</label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                            required>
                        <label for="password">Password</label>
                    </div>

                    <!-- Submit Button -->
                    <button class="btn btn-primary w-100 mb-3" type="submit">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </button>

                    <!-- Social Login Divider -->
                    <div class="divider">or create new account</div>

                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="text-muted">Don't have an account? <a href="register"
                                class="text-decoration-none">Sign up</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/bootstrap.bundle.min.js"></script>


    <script>
        $(document).ready(function () {
            // Form validation
            $('#loginForm').submit(function (e) {
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                let isValid = true;

                // Email validation
                if (!$('#email').val()) {
                    $('#email').addClass('is-invalid');
                    $('#email').after('<div class="invalid-feedback">Please enter your email</div>');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('#email').val())) {
                    $('#email').addClass('is-invalid');
                    $('#email').after('<div class="invalid-feedback">Please enter a valid email</div>');
                    isValid = false;
                }

                // Password validation
                if (!$('#password').val()) {
                    $('#password').addClass('is-invalid');
                    $('#password').after('<div class="invalid-feedback">Please enter your password</div>');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();

                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 300);
                }
            });
        });
    </script>
</body>

</html>