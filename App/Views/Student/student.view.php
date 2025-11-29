<?php
/** @var \Framework\Support\View $view */
/** @var \Framework\Auth\AppUser $user */
// Set the layout to the main root layout
$view->setLayout('student');
?>

<div class="row">
    <div class="col-12">
        <h1>Vitajte, <?= htmlspecialchars($user->getName() ?? 'Hosť') ?></h1>
        <p class="lead">Toto je hlavný dashboard. Nižšie nájdete rýchly prehľad podľa vašej role.</p>
    </div>
</div>

<div class="row mt-4">

    <div class="col-12">
        <h3>Študentský dashboard</h3>
        <p>Prehľad vašich zápisov a dosiahnutých známok.</p>
        <a href="?c=enrollments" class="btn btn-primary">Moje zápisy</a>
    </div>
</div>