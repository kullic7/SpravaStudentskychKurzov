<?php
/** @var \App\Models\User $userModel */
/** @var array|null $errors */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');
$title = 'Upraviť používateľa';

?>

<div class="card mx-auto" style="max-width:800px;">
    <div class="card-body">
        <h2 class="mb-4">Upraviť používateľa</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="editUserForm" method="post" action="<?= htmlspecialchars($link->url('admin.updateUser')) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($userModel->id) ?>">

            <div class="mb-3">
                <label class="form-label" for="firstName">Meno</label>
                <input id="firstName" type="text" name="firstName" class="form-control" value="<?= htmlspecialchars($userModel->firstName ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="lastName">Priezvisko</label>
                <input id="lastName" type="text" name="lastName" class="form-control" value="<?= htmlspecialchars($userModel->lastName ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="<?= htmlspecialchars($userModel->email ?? '') ?>" required>
            </div>

            <?php // role editable only for admins ?>
            <?php $currentUser = $this->app->getAuthenticator()->getUser(); try { $isAdmin = $currentUser->getRole() === 'admin'; } catch (\Throwable $_) { $isAdmin = false; } ?>
            <?php if ($isAdmin): ?>
                <div class="mb-3">
                    <label class="form-label" for="role">Rola</label>
                    <input id="role" type="text" name="role" class="form-control" value="<?= htmlspecialchars($userModel->role ?? '') ?>">
                </div>
            <?php else: ?>
                <div class="mb-3">
                    <label class="form-label">Rola</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($userModel->role ?? '') ?>" disabled>
                </div>
            <?php endif; ?>

            <hr>
            <p class="mb-2">Zmena hesla (nepovinné)</p>
            <div class="mb-3">
                <label class="form-label" for="password">Nové heslo</label>
                <input id="password" type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label" for="passwordConfirm">Potvrď heslo</label>
                <input id="passwordConfirm" type="password" name="passwordConfirm" class="form-control">
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= htmlspecialchars($link->url('admin.pouzivatelia')) ?>" class="btn btn-outline-secondary">Späť</a>
                <button id="editUserSubmit" type="submit" class="btn btn-primary">Uložiť zmeny</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= $link->asset('js/scripts.js') ?>"></script>

