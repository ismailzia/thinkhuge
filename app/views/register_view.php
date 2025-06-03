<?php
    use App\core\Flash;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - App</title>
    <!-- Bootstrap 5 CSS -->
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
                    <h2 class="fw-bold mb-3">Create Account</h2>
                    <p class="text-muted">Create account by completing the form bellow</p>
                </div>

                <form id="registerForm" action="auth/register" method="post">

                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="firstName" name="first_name"
                                    placeholder="John" required>
                                <label for="firstName">First Name</label>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Doe"
                                    required>
                                <label for="lastName">Last Name</label>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="name@example.com" required>
                                <label for="email">Email Address</label>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password"
                                    pattern="^(?=.*[A-Z])(?=.*\d).{8,}$" placeholder="Password" required minlength="8">
                                <label for="password">Password</label>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="confirmPassword"
                                    name="password_confirmation" placeholder="Confirm Password" required>
                                <label for="confirmPassword">Confirm Password</label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-person-plus me-2"></i> Register Now
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="col-12 text-center mt-4">
                            <p class="text-muted">Already have an account? <a href="login">Sign In</a></p>
                        </div>
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
            $('#registerForm').submit(function (e) {
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                let isValid = true;

                // Password match validation
                if ($('#password').val() !== $('#confirmPassword').val()) {
                    $('#confirmPassword').addClass('is-invalid');
                    $('#confirmPassword').after('<div class="invalid-feedback">Passwords do not match</div>');
                    isValid = false;
                }

                // Password length validation
                if ($('#password').val().length < 8) {
                    $('#password').addClass('is-invalid');
                    $('#password').after('<div class="invalid-feedback">Password must be at least 8 characters</div>');
                    isValid = false;
                }

                // Terms checkbox validation


                if (!isValid) {
                    e.preventDefault();

                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 300);
                }
            });

            // Real-time password match indicator
            $('#confirmPassword').keyup(function () {
                if ($('#password').val() !== $(this).val()) {
                    $(this).addClass('is-invalid');
                    if (!$('#confirmPassword').next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
        });
    </script>
</body>

</html>