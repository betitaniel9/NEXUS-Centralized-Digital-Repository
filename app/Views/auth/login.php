<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS Centralized Digital Repository</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome (eye icon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
</head>
<body>
<!-- Background beams moving along the grid (decorative) -->
    <div class="grid-beams" aria-hidden="true">
        <span class="beam beam-h" style="--line:3;  --dur:1s;  --delay:8s;"></span>
        <span class="beam beam-h" style="--line:8;  --dur:8s;  --delay:2.5s;"></span>
        <span class="beam beam-h" style="--line:13; --dur:7s;  --delay:4s;"></span>
        <span class="beam beam-v" style="--line:4;  --dur:7s;  --delay:1s;"></span>
        <span class="beam beam-v" style="--line:11; --dur:1s;  --delay:3s;"></span>
        <span class="beam beam-v" style="--line:19; --dur:6s;  --delay:5s;"></span>
        <span class="beam beam-v" style="--line:27; --dur:2s;  --delay:0.5s;"></span>
    </div>

<!-- Logo -->
    <div class="logo-wrapper d-flex justify-content-center align-items-center">
        <div class="logo-container text-center pb-1 w-50">
            <img src="<?= base_url('assets/images/NEXUS-logo.png') ?>" alt="Logo" class="logo w-25 text-center">
        </div>
    </div>
  
      <div class="login-container">
        <div class="card login-card">

            <div class="card-body">
                <!-- Login Header -->
                <div class="login-header text-center">
                    <h2 class="card-title">Sign In</h2>
                    <p>Use your gsuite to continue</p>
                </div>

                <!-- Login Form -->
                <form action="<?= base_url('auth/login') ?>" method="post">

                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="example@g.batstate-u.edu.ph"
                            required
                        >
                    </div>

                    <!-- Password -->
                    <div class="form-group mb-3">

                        <div class="password-label d-flex justify-content-between align-items-end">
                            <label for="password" class="form-label">Password</label>
                            <a href="<?= base_url('forgot-password') ?>" class="forgot-pass">Forgot password?</a>
                        </div>

                        <div class="password-wrapper">
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Show password">
                                <i id="toggleIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>

                    </div>

                    <!-- Remember Me -->
                    <div class="remember-container mb-3">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                        >
                        <label for="remember">Keep me signed in</label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn login-btn w-100">
                        LOG IN
                    </button>

                    <!-- Sign Up Button -->
                    <a href="<?= base_url('register') ?>" class="btn signup-btn w-100 mt-2">
                        CREATE ACCOUNT
                    </a>

                </form>

                <!-- Footer Notice -->
                <div class="login-footer text-center">

                    <p class="restriction">
                        This website is strictly prohibited for students outside CICS.
                    </p>

                    <p class="contact">
                        Got any concerns?
                        <a href="mailto:cics@batstate-u.edu.ph">Contact Council</a>
                    </p>

                </div>

            </div>
        </div>

    </div>

    <!-- Password Visibility Script -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const icon = document.getElementById("toggleIcon");

            if (password.type === "password") {
                password.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                password.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

</body>
</html>