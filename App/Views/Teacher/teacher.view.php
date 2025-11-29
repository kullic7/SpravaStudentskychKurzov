<?php
/** @var \Framework\Support\View $view */
/** @var \Framework\Auth\AppUser $user */
// Set the layout to the main root layout
$view->setLayout('teacher');
?>

<div class="row">
    <div class="col-12">
        <h1>Vitajte, <?= htmlspecialchars($user->getName() ?? 'Hosť') ?></h1>
        <p class="lead">Toto je hlavný dashboard. Nižšie nájdete rýchly prehľad podľa vašej role.</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h3>Učiteľský dashboard</h3>
        <p>Prehľad vašich kurzov a zapísaných študentov.</p>
        <a href="?c=my-courses" class="btn btn-primary">Moje kurzy</a>
    </div>

</div>