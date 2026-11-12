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
    <link rel="stylesheet" href="<?= base_url('assets/css/flash.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/register.css') ?>">

    <!-- Arriving from a Login / Register link: reveal this page in a circle from where it was clicked.
         Modern browsers draw the circle over the previous page (view transition) as soon as this page is ready;
         older browsers fall back to revealing this page from a dark background. -->
    <script>
        (function () {
            let saved = null;
            try {
                saved = JSON.parse(sessionStorage.getItem('nexusWipe') || 'null');
                sessionStorage.removeItem('nexusWipe');
            } catch (e) { /* storage blocked */ }

            const root = document.documentElement;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const useCircle = saved && !reduce;

            if (useCircle) {
                root.style.setProperty('--wx', (saved.x * window.innerWidth) + 'px');
                root.style.setProperty('--wy', (saved.y * window.innerHeight) + 'px');
            }

            if ('CSSViewTransitionRule' in window) {
                window.addEventListener('pagereveal', e => {
                    if (e.viewTransition) {
                        if (useCircle) root.classList.add('vt-wipe');
                        else e.viewTransition.skipTransition(); // no click origin: just show the page
                    } else if (useCircle) {
                        root.classList.add('wipe-in');
                    }
                });
            } else if (useCircle) {
                root.classList.add('wipe-in');
            }
        })();
    </script>
</head>
<body>
    <?php
    // Per-field validation errors from the last failed sign-up attempt
    $errors   = session()->getFlashdata('errors') ?? [];
    $invalid  = fn (string $field) => isset($errors[$field]) ? ' is-invalid' : '';
    $errorFor = fn (string $field) => isset($errors[$field])
        ? '<small class="field-error">' . esc($errors[$field]) . '</small>'
        : '';
    ?>

    <!-- Login / register result pop-up -->
    <?= view('partials/flash') ?>

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

                    <!-- First and last name side by side (one row, two half-width columns) -->
                    <div class="row g-3 mb-3">
                        <!-- First Name -->
                        <div class="col-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input
                                type="text"
                                class="form-control<?= $invalid('first_name') ?>"
                                id="first_name"
                                name="first_name"
                                placeholder="Juan"
                                value="<?= esc(old('first_name') ?? '') ?>"
                                autocomplete="given-name"
                                required
                            >
                            <?= $errorFor('first_name') ?>
                        </div>

                        <!-- Last Name -->
                        <div class="col-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input
                                type="text"
                                class="form-control<?= $invalid('last_name') ?>"
                                id="last_name"
                                name="last_name"
                                placeholder="Dela Cruz"
                                value="<?= esc(old('last_name') ?? '') ?>"
                                autocomplete="family-name"
                                required
                            >
                            <?= $errorFor('last_name') ?>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control<?= $invalid('email') ?>"
                            id="email"
                            name="email"
                            placeholder="example@g.batstate-u.edu.ph"
                            value="<?= esc(old('email') ?? '') ?>"
                            pattern="[^@\s]+@g\.batstate-u\.edu\.ph"
                            title="Use your G Suite account (ending in @g.batstate-u.edu.ph)"
                            autocomplete="email"
                            required
                        >
                        <?= $errorFor('email') ?>
                    </div>

                    <!-- Password -->
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                class="form-control<?= $invalid('password') ?>"
                                id="password"
                                name="password"
                                placeholder="At least 8 characters"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <?= $errorFor('password') ?>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                class="form-control<?= $invalid('confirm_password') ?>"
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
                        <small id="matchMessage" class="match-message"><?= esc($errors['confirm_password'] ?? '') ?></small>
                    </div>

                    <!-- Terms -->
                    <div class="remember-container mb-3">
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            required
                        >
                        <label for="terms">
                            I agree to the
                            <a href="#" class="terms-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                        </label>
                    </div>
                    <?= $errorFor('terms') ?>

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
                        <a href="<?= base_url('/login') ?>">Sign in</a>
                    </p>

                    <p class="contact">
                        Got any concerns?
                        <a href="mailto:cics@batstate-u.edu.ph">Contact Council</a>
                    </p>

                </div>

            </div>
        </div>
    </div>

    <!-- Terms and Conditions pop-up -->
    <div class="modal fade terms-modal" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="termsModalLabel">Terms and Conditions</h2>
                        <p class="terms-date"><strong>Effective Date:</strong> Hulaan mooow</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body terms-body">
                    <h3>1. Acceptance of Terms</h3>
                    <p>By accessing and using this website, you acknowledge that you have read, understood, and agreed to comply with these Terms and Conditions. If you do not agree with any part of these terms, please discontinue using the website.</p>

                    <h3>2. Use of the Website</h3>
                    <p>You agree to use this website only for lawful purposes and in accordance with these Terms and Conditions. You must not:</p>
                    <ul>
                        <li>Use the website for fraudulent, harmful, or illegal activities.</li>
                        <li>Attempt to gain unauthorized access to the website or its systems.</li>
                        <li>Disrupt, damage, or interfere with the website's functionality.</li>
                        <li>Upload or distribute malicious software, harmful files, or inappropriate content.</li>
                        <li>Misuse any features, services, or information provided by the website.</li>
                    </ul>

                    <h3>3. User Accounts</h3>
                    <p>Some features of this website may require users to create an account. By registering, you agree to:</p>
                    <ul>
                        <li>Provide accurate and complete information.</li>
                        <li>Maintain the confidentiality of your login credentials.</li>
                        <li>Take responsibility for activities performed through your account.</li>
                        <li>Notify the website administrators of any unauthorized access or suspicious activity.</li>
                    </ul>
                    <p>The website administrators reserve the right to suspend or terminate accounts that violate these Terms and Conditions.</p>

                    <h3>4. Intellectual Property</h3>
                    <p>All website content, including text, graphics, logos, designs, images, and other materials, is protected by applicable intellectual property laws unless otherwise stated.</p>
                    <p>Users may not reproduce, distribute, modify, or use website materials for commercial purposes without prior authorization from the rightful owner.</p>

                    <h3>5. User-Submitted Content</h3>
                    <p>If the website allows users to submit information, documents, images, or other materials, you agree that:</p>
                    <ul>
                        <li>You have the necessary rights or authorization to submit the content.</li>
                        <li>Your submissions do not violate the rights of other individuals or organizations.</li>
                        <li>Your submitted content does not contain harmful, illegal, or misleading material.</li>
                        <li>The website administrators may review, moderate, approve, reject, or remove submitted content when necessary.</li>
                    </ul>

                    <h3>6. Privacy and Data Protection</h3>
                    <p>The collection, processing, storage, and use of personal information are governed by the website's Privacy Policy.</p>
                    <p>By using this website, you acknowledge that certain information may be collected to provide and improve its services, maintain security, and fulfill operational requirements.</p>
                    <p>Personal information will be handled in accordance with applicable data protection laws and regulations.</p>

                    <h3>7. Third-Party Links and Services</h3>
                    <p>This website may contain links to external websites, applications, or third-party services. These links are provided for convenience and informational purposes.</p>
                    <p>We are not responsible for the content, policies, security, or practices of third-party websites. Users are encouraged to review the terms and privacy policies of external services they access.</p>

                    <h3>8. Website Availability</h3>
                    <p>We aim to keep the website accessible and operational. However, we do not guarantee uninterrupted availability.</p>
                    <p>The website may occasionally become unavailable due to maintenance, technical issues, security updates, or circumstances beyond our control.</p>

                    <h3>9. Limitation of Liability</h3>
                    <p>To the extent permitted by applicable law, the website owners, administrators, and developers shall not be held liable for damages, losses, or inconveniences resulting from the use or inability to use the website.</p>
                    <p>Users are responsible for ensuring that their use of the website complies with these Terms and Conditions.</p>

                    <h3>10. Suspension and Termination</h3>
                    <p>We reserve the right to restrict, suspend, or terminate access to the website or specific user accounts if a user violates these Terms and Conditions, engages in unauthorized activities, or threatens the security and integrity of the platform.</p>

                    <h3>11. Changes to These Terms</h3>
                    <p>We reserve the right to update or modify these Terms and Conditions at any time. Any changes will take effect once published on this website.</p>
                    <p>Users are encouraged to review this page periodically to remain informed of any updates.</p>

                    <h3>12. Governing Law</h3>
                    <p>These Terms and Conditions shall be governed by and interpreted in accordance with the applicable laws of the Republic of the Philippines.</p>
                    <p>Any disputes arising from the use of this website shall be handled in accordance with applicable Philippine laws.</p>

                    <h3>13. Contact Information</h3>
                    <p>If you have questions, concerns, or requests regarding these Terms and Conditions, you may contact the website administrators through the contact information provided on this website.</p>

                    <p class="terms-closing"><strong>*** By continuing to access and use this website, you acknowledge that you have read and agreed to these Terms and Conditions.***</strong></p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn terms-btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn terms-btn-primary" id="acceptTerms" data-bs-dismiss="modal">I agree</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (needed for the Terms pop-up) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // "I agree" in the Terms pop-up ticks the checkbox
        document.getElementById("acceptTerms").addEventListener("click", () => {
            document.getElementById("terms").checked = true;
        });

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

    <!-- Circle wipe between Login and Register -->
    <script src="<?= base_url('assets/js/page-transition.js') ?>"></script>
</body>
</html>
