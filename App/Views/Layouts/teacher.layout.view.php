<?php
/** @var string $contentHTML */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Security\LoggedUser|null $user */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Správa kurzov' ?></title>

    <link rel="stylesheet" href="/css/logoStyle.css?x=123">
    <link rel="stylesheet" href="/css/mainBackgroundStyle.css?x=123">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body class="main-bg">

<header class="ano">
    <nav class="navbar navbar-expand-md navbar-light bg-transparent py-3">
        <div class="container">

            <!-- LOGO -->
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="<?= $link->asset('images/logo.png') ?>"
                     alt="Logo"
                     class="login-logo"
                >
            </a>

            <!-- HAMBURGER -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- MENU -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto" style="font-size: 1.2rem; font-weight: 700; padding: 8px 15px; ">

                    <li class="nav-item"><a class="nav-link" href="#">Domov</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Kurzy</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Študenti</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Profil</a></li>

                    <li class="nav-item">
                        <a class="nav-link text-danger fw-semibold" href="#">Odhlásiť sa</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
</header>



<!-- CONTENT -->
<main class="container py-5">
    <?= $contentHTML ?>
</main>

</body>
</html>