<?php
/** @var array|null $teachers */
/** @var array|null $errors */
/** @var array|null $posted */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

use App\Configuration;

$view->setLayout(Configuration::HOME_LAYOUT);

$title = 'Vytvoriť kurz';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Vytvoriť kurz</h1>

        <?php if (!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($link->url('admin.createCoursePost')) ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Názov</label>
                <input id="name" name="name" type="text" class="form-control" value="<?= htmlspecialchars($posted['name'] ?? '') ?>" required pattern="^[A-Za-z0-9 ]+$"
                       title="Názov môže obsahovať len písmená, čísla a medzery">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Popis</label>
                <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($posted['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="credits" class="form-label">Kredity</label>
                <input id="credits" name="credits" type="number" min="1" max="6" class="form-control" value="<?= htmlspecialchars($posted['credits'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="teacherId" class="form-label">Učiteľ (voliteľné)</label>
                <select id="teacherId" name="teacherId" class="form-select">
                    <option value="">-- Žiadny --</option>
                    <?php if (!empty($teachers) && is_array($teachers)): ?>
                        <?php foreach ($teachers as $t):
                            $u = $t->getUser();
                            $label = $u ? (($u->firstName ?? '') . ' ' . ($u->lastName ?? '')) : ('Učiteľ #' . ($t->id ?? ''));
                            $selected = (isset($posted['teacherId']) && (string)$posted['teacherId'] === (string)($t->id ?? '')) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($t->id) ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Vytvoriť</button>
                <a href="<?= htmlspecialchars($link->url('course.kurzy')) ?>" class="btn btn-secondary">Zrušiť</a>
            </div>
        </form>
    </div>
</div>

