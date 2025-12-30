<?php
/** @var array|null $errors */
/** @var array|null $posted */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');


function pv(string $key, $default = ''): string
{
    global $posted;
    if ($posted !== null && array_key_exists($key, $posted)) {
        return htmlspecialchars((string)$posted[$key]);
    }
    return htmlspecialchars((string)$default);
}
?>

<div class="card mx-auto" style="max-width:800px;">
    <div class="card-body">
        <h2 class="mb-4">Vytvoriť používateľa</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($link->url('admin.createUserPost')) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">Meno</label>
                    <input id="firstName" name="firstName" class="form-control" value="<?= pv('firstName') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label">Priezvisko</label>
                    <input id="lastName" name="lastName" class="form-control" value="<?= pv('lastName') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="<?= pv('email') ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Heslo</label>
                    <input id="password" type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="passwordConfirm" class="form-label">Potvrď heslo</label>
                    <input id="passwordConfirm" type="password" name="passwordConfirm" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Rola</label>
                <select id="role" name="role" class="form-control">
                    <option value="admin" <?= pv('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="student" <?= pv('role', 'student') === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="teacher" <?= pv('role') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                </select>
            </div>

            <hr>

            <div id="studentFields" style="display:none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="studentNumber" class="form-label">Študijné číslo</label>
                        <input id="studentNumber" name="studentNumber" pattern="^S\d{4}$" class="form-control" value="<?= pv('studentNumber') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="year" class="form-label">Ročník</label>
                        <input id="year" name="year" type="number" min="1" max="4" class="form-control" value="<?= pv('year') ?>">
                    </div>
                </div>
            </div>

            <div id="teacherFields" style="display:none;">
                <div class="mb-3">
                    <label for="department" class="form-label">Oddelenie</label>
                    <input id="department" name="department" class="form-control" value="<?= pv('department') ?>" >
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($link->url('admin.pouzivatelia')) ?>">Späť</a>
                <button type="submit" class="btn btn-primary">Vytvoriť</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= $link->asset('js/createUserScript.js') ?>"></script>
