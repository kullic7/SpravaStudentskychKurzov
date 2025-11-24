<?php /** @var string $contentHTML */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Prihlásenie' ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $link->asset('css/styl.css') ?>">
</head>

<body class="auth-bg">

    <div class="login-logo-wrapper">
        <img src="<?= $link->asset('images/logo.png') ?>" class="login-logo" alt="Logo">
    </div>

    <div class="auth-wrapper d-flex justify-content-center align-items-center">
        <div class="auth-card shadow-lg">
            <?= $contentHTML ?>
        </div>
    </div>

</body>
</html>
