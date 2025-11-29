<?php
/** @var \Framework\Support\View $view */
/** @var \Framework\Auth\AppUser $user */
// Set the layout to the main root layout
$view->setLayout('admin');
?>

<div class="row">
    <div class="col-12">
        <h1>Vitajte, <?= htmlspecialchars($user->getName() ?? 'Hosť') ?></h1>
        <p class="lead">Toto je hlavný dashboard. Nižšie nájdete rýchly prehľad podľa vašej role.</p>
    </div>
</div>

<div class="row mt-4">
        <div class="col-12">
            <h3>Admin dashboard</h3>
            <p>Sem patrí prehľad používateľov, správa kurzov a systémové nastavenia.</p>
            <a href="?c=users" class="btn btn-primary">Spravovať používateľov</a>
        </div>

</div>
