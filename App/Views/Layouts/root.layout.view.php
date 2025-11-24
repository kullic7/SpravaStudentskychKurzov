<?php /** @var string $contentHTML */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Správa kurzov' ?></title>

    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="?c=home">Správa kurzov</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item"><a class="nav-link" href="?c=courses">Kurzy</a></li>

                <?php if ($user->role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="?c=users">Používatelia</a></li>
                <?php endif; ?>

                <?php if ($user->role === 'teacher'): ?>
                    <li class="nav-item"><a class="nav-link" href="?c=my-courses">Moje kurzy</a></li>
                <?php endif; ?>

                <?php if ($user->role === 'student'): ?>
                    <li class="nav-item"><a class="nav-link" href="?c=enrollments">Moje zápisy</a></li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link" href="?c=auth&a=logout">Odhlásiť sa</a></li>

            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">
    <?= $contentHTML ?>
</main>

</body>
</html>

