<?php
/** @var \App\Models\User $editUser */
/** @var \App\Models\Student|null $student */
/** @var \App\Models\Teacher|null $teacher */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');
$title = 'Upraviť používateľa';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Upraviť používateľa</h1>

        <form method="post" action="<?= htmlspecialchars($link->url('admin.editUser')) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($editUser->id ?? '') ?>">

            <div class="mb-3">
                <label for="firstName" class="form-label">Meno</label>
                <input id="firstName" type="text" name="firstName" class="form-control"
                       value="<?= htmlspecialchars($editUser->firstName ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="lastName" class="form-label">Priezvisko</label>
                <input id="lastName" type="text" name="lastName" class="form-control"
                       value="<?= htmlspecialchars($editUser->lastName ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($editUser->email ?? '') ?>" required>
            </div>



            <!-- ==== ŠTUDENT ==== -->
            <div id="studentFields" style="display: <?= (($editUser->role ?? '') === 'student') ? 'block' : 'none' ?>;">
                <div class="mb-3">
                    <label for="studentNumber" class="form-label">Študijné číslo</label>
                    <input id="studentNumber" type="text" name="studentNumber" class="form-control"
                           value="<?= htmlspecialchars($student->studentNumber ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="year" class="form-label">Ročník</label>
                    <input id="year" type="number" name="year" class="form-control"
                           value="<?= htmlspecialchars($student->year ?? '') ?>">
                </div>
            </div>

            <!-- ==== UČITEĽ ==== -->
            <div id="teacherFields" style="display: <?= (($editUser->role ?? '') === 'teacher') ? 'block' : 'none' ?>;">
                <div class="mb-3">
                    <label for="department" class="form-label">Oddelenie</label>
                    <input id="department" type="text" name="department" class="form-control"
                           value="<?= htmlspecialchars($teacher->department ?? '') ?>">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Uložiť</button>
                <a href="<?= htmlspecialchars($link->url('admin.pouzivatelia')) ?>" class="btn btn-outline-secondary">
                    Späť
                </a>
            </div>
        </form>
    </div>
</div>

