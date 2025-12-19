<?php
/** @var array $courses */
/** @var array $courseTeachers */
/** @var array $allTeachers */
/** @var \Framework\Support\View $view */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\LoggedUser|null $user */

// Use the home layout for this view
$view->setLayout('home');

// Page title
$title = 'Kurzy';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zoznam kurzov</h1>

        <a href="<?= htmlspecialchars($link->url('admin.createCourse')) ?>" class="btn btn-sm btn-primary mb-3">Vytvoriť kurz</a>

        <?php if (empty($courses)): ?>
            <p>Žiadne kurzy neboli nájdené.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped" id="coursesTable">
                    <thead>
                        <tr>
                            <th>Názov</th>
                            <th>Učiteľ</th>
                            <th>Email</th>
                            <th>Kredity</th>
                            <th>Popis</th>
                            <th>Akcie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course):
                            // Use the precomputed courseTeachers map attached by the controller
                            $teachers = $courseTeachers[$course->id] ?? [];

                            $teacherNames = '-';
                            $teacherEmails = '-';
                            $teacherIdForRow = '';

                            if (!empty($teachers)) {
                                $names = [];
                                $emails = [];
                                foreach ($teachers as $tEntry) {
                                    $names[] = $tEntry->name ?? ($tEntry->user ? ($tEntry->user->firstName . ' ' . $tEntry->user->lastName) : '-');
                                    $emails[] = $tEntry->email ?? ($tEntry->user->email ?? '-');
                                    $teacherIdForRow = $tEntry->teacher->id ?? $tEntry->teacher->userId ?? $tEntry->teacher->id ?? '';
                                }
                                $teacherNames = implode(', ', $names);
                                $teacherEmails = implode(', ', $emails);
                            }

                            // Prepare data attributes for JS editor
                            $dataAttrs = 'data-course-id="' . htmlspecialchars($course->id) . '" '
                                . 'data-teacher-id="' . htmlspecialchars($course->teacherId ?? $teacherIdForRow) . '" '
                                . 'data-credits="' . htmlspecialchars($course->credits ?? '') . '" '
                                . 'data-description="' . htmlspecialchars($course->description ?? '') . '"';
                        ?>
                        <tr <?= $dataAttrs ?>>
                            <td data-field="name"><span class="value"><?= htmlspecialchars($course->name) ?></span></td>
                            <td data-field="teacher"><span class="value"><?= htmlspecialchars($teacherNames) ?></span></td>
                            <td data-field="teacherEmail"><span class="value"><?= htmlspecialchars($teacherEmails) ?></span></td>
                            <td data-field="credits"><span class="value"><?= htmlspecialchars($course->credits) ?></span></td>
                            <td data-field="description"><span class="value"><?= htmlspecialchars($course->description) ?></span></td>
                            <td class="actions">
                                <a href="<?= htmlspecialchars($link->url('admin.editCourse', ['id' => $course->id])) ?>" class="btn btn-sm btn-secondary">Upraviť</a>
                                <form action="<?= htmlspecialchars($link->url('admin.deleteCourse')) ?>" method="post" onsubmit="return confirm('Naozaj chcete zmazať tento kurz?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($course->id) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Zmazať</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
