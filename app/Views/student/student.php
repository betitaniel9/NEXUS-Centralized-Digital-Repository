<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student | NEXUS Centralized Digital Repository</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/flash.css') ?>">
</head>
<body>
    <!-- "Login successful" pop-up -->
    <?= view('partials/flash') ?>

    <!-- Placeholder student page: replace with the real student side -->
    <div class="login-container">
        <div class="card login-card">
            <div class="card-body">
                <div class="login-header text-center">
                    <h2 class="card-title">Welcome, <?= esc($firstName) ?>!</h2>
                    <p class="mb-1"><?= esc($firstName . ' ' . $lastName) ?></p>
                    <p class="text-muted mb-1"><?= esc($email) ?></p>
                    <p class="text-muted">Role: <?= esc(implode(', ', $roles) ?: 'None') ?></p>
                </div>

                <form action="<?= base_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn login-btn w-100">LOG OUT</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
