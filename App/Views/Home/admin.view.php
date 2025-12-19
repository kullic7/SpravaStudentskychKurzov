<?php
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Support\View $view */
$view->setLayout('home');
?>

<div class="mt-5 pt-4 text-center">
    <h1>Vitajte, <?= htmlspecialchars($user->getName() ?? 'Hosť') ?></h1>
    <p class="lead">Ste prihlásený ako administrátor</p>
</div>

<div class="row justify-content-center mt-5">

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="border p-4 text-center">
            <h5>Kurzy</h5>
            <p class="fw-bold fs-4"><?= htmlspecialchars($courseCount ?? 0) ?></p>
            <a href="<?= htmlspecialchars($link->url('admin.kurzy')) ?>" class="btn btn-outline-dark">Zobraziť</a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="border p-4 text-center">
            <h5>Používatelia</h5>
            <p class="fw-bold fs-4"><?= htmlspecialchars($userCount ?? 0) ?></p>
            <a href="<?= htmlspecialchars($link->url('admin.pouzivatelia')) ?>" class="btn btn-outline-dark">Zobraziť</a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="border p-4 text-center">
            <h5>Zápisy</h5>
            <p class="fw-bold fs-4"><?= htmlspecialchars($enrollmentCount ?? 0) ?></p>
            <a href="<?= htmlspecialchars($link->url('admin.zapisy')) ?>" class="btn btn-outline-dark">Zobraziť</a>
        </div>
    </div>

</div>
