<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | NEXUS Centralized Digital Repository</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome (eye icon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <!-- Shared login styles + sign-up tweaks -->
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/register.css') ?>">
</head>
<body>
<!-- Background beams moving along the grid (decorative) -->
    <div class="grid-beams" aria-hidden="true">
        <span class="beam beam-h" style="--line:3;  --dur:6s;  --delay:0s;"></span>
        <span class="beam beam-h" style="--line:8;  --dur:8s;  --delay:2.5s;"></span>
        <span class="beam beam-h" style="--line:13; --dur:7s;  --delay:4s;"></span>
        <span class="beam beam-v" style="--line:4;  --dur:7s;  --delay:1s;"></span>
        <span class="beam beam-v" style="--line:11; --dur:9s;  --delay:3s;"></span>
        <span class="beam beam-v" style="--line:19; --dur:6s;  --delay:5s;"></span>
        <span class="beam beam-v" style="--line:27; --dur:8s;  --delay:0.5s;"></span>
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
                <!-- Header -->
                <div class="login-header text-center">
                    <h2 class="card-title">Sign Up</h2>
                    <p>Create your account using your gsuite</p>
                </div>

                <!-- Sign Up Form -->
                <form action="<?= base_url('auth/register') ?>" method="post">

                    <?= csrf_field() ?>

                    <!-- Full Name -->
                    <div class="form-group mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="fullname"
                            name="fullname"
                            placeholder="Juan Dela Cruz"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <!-- Email -->
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="example@g.batstate-u.edu.ph"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <!-- Password -->
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Re-enter your password"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small id="matchMessage" class="match-message"></small>
                    </div>

                    <!-- Terms -->
                    <div class="remember-container mb-3">
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            required
                        >
                        <label for="terms">I agree to the terms and privacy policy</label>
                    </div>

                    <!-- Sign Up Button -->
                    <button type="submit" class="btn login-btn w-100">
                        SIGN UP
                    </button>

                </form>

                <!-- Footer -->
                <div class="login-footer text-center">

                    <p class="restriction">
                        This website is strictly prohibited for students outside CICS.
                    </p>

                    <p class="switch-auth">
                        Already have an account?
                        <a href="<?= base_url('/') ?>">Sign in</a>
                    </p>

                    <p class="contact">
                        Got any concerns?
                        <a href="mailto:cics@batstate-u.edu.ph">Contact Council</a>
                    </p>

                </div>

            </div>
        </div>
    </div>

    <script>
        // Show / hide a password field
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector("i");
            const show = field.type === "password";

            field.type = show ? "text" : "password";
            icon.classList.toggle("fa-eye", !show);
            icon.classList.toggle("fa-eye-slash", show);
            button.setAttribute("aria-label", show ? "Hide password" : "Show password");
        }

        // Check that both passwords match
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm_password");
        const matchMessage = document.getElementById("matchMessage");

        function checkMatch() {
            if (confirmPassword.value === "") {
                matchMessage.textContent = "";
                confirmPassword.setCustomValidity("");
                return;
            }

            const matches = password.value === confirmPassword.value;
            matchMessage.textContent = matches ? "Passwords match" : "Passwords do not match";
            matchMessage.classList.toggle("ok", matches);
            confirmPassword.setCustomValidity(matches ? "" : "Passwords do not match");
        }

        password.addEventListener("input", checkMatch);
        confirmPassword.addEventListener("input", checkMatch);
    </script>

</body>
</html>
