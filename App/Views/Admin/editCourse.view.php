<?php
/** @var \App\Models\Course $course */
/** @var array|null $teachers */
/** @var array|null $errors */
/** @var array|null $posted */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');
$title = 'Upraviť kurz';

// helper to get posted value or course property
function val($posted, $course, $key, $prop = null) {
    if (isset($posted[$key])) return $posted[$key];
    $prop = $prop ?? $key;
    return $course->{$prop} ?? '';
}
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Upraviť kurz</h1>

        <?php if (!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($link->url('admin.updateCoursePost')) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($course->id ?? '') ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Názov</label>
                <input id="name" name="name" type="text" class="form-control" required value="<?= htmlspecialchars(val($posted ?? [], $course ?? null, 'name')) ?>">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Popis</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars(val($posted ?? [], $course ?? null, 'description')) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="credits" class="form-label">Kredity</label>
                <input id="credits" name="credits" type="number" min="0" class="form-control" value="<?= htmlspecialchars(val($posted ?? [], $course ?? null, 'credits')) ?>">
            </div>

            <div class="mb-3">
                <label for="teacherId" class="form-label">Učiteľ (voliteľné)</label>
                <select id="teacherId" name="teacherId" class="form-select">
                    <option value="">-- Žiadny --</option>
                    <?php if (!empty($teachers) && is_array($teachers)): ?>
                        <?php foreach ($teachers as $t):
                            $u = $t->getUser();
                            $label = $u ? (($u->firstName ?? '') . ' ' . ($u->lastName ?? '')) : ('Učiteľ #' . ($t->id ?? ''));
                            $selVal = (string)val($posted ?? [], $course ?? null, 'teacherId');
                            $isSelected = ($selVal !== '' && (string)$t->id === $selVal) || (($selVal === '') && isset($course->teacherId) && (string)$course->teacherId === (string)$t->id);
                        ?>
                            <option value="<?= htmlspecialchars($t->id) ?>" <?= $isSelected ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Uložiť</button>
                <a href="<?= htmlspecialchars($link->url('admin.kurzy')) ?>" class="btn btn-secondary">Zrušiť</a>
            </div>
        </form>
    </div>
</div>

