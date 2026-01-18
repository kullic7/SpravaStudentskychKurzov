<?php
/** @var array $courses */
/** @var array $courseTeachers */
/** @var array $allTeachers */
/** @var bool $isAdmin */
/** @var bool $isStudent */
/** @var array $studentEnrollmentsMap */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');
$title = 'Kurzy';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zoznam kurzov</h1>

        <?php if (!empty($isAdmin)): ?>
            <a href="<?= htmlspecialchars($link->url('admin.createCourse')) ?>" class="btn btn-sm btn-primary mb-3">Vytvoriť kurz</a>
        <?php endif; ?>

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
                            <?php if (!empty($isAdmin)): ?><th>Akcie</th><?php elseif (!empty($isStudent)): ?><th>Akcia</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course):
                            $teacher = $courseTeachers[$course->id] ?? null;
                            $teacherNames = '-';
                            $teacherEmails = '-';
                            if ($teacher) {
                                $teacherNames = $teacher->name ?? '-';
                                $teacherEmails = $teacher->email ?? '-';
                            }

                            $alreadyEnrolled = false;
                            $enrollmentStatus = null;
                            if (!empty($isStudent) && !empty($studentEnrollmentsMap) && is_array($studentEnrollmentsMap)) {
                                if (array_key_exists($course->id, $studentEnrollmentsMap)) {
                                    $alreadyEnrolled = true;
                                    $enrollmentStatus = $studentEnrollmentsMap[$course->id];
                                }
                            }
                        ?>
                        <tr>
                            <td data-field="name"><span class="value"><?= htmlspecialchars($course->name) ?></span></td>
                            <td data-field="teacher"><span class="value"><?= htmlspecialchars($teacherNames) ?></span></td>
                            <td data-field="teacherEmail"><span class="value"><?= htmlspecialchars($teacherEmails) ?></span></td>
                            <td data-field="credits"><span class="value"><?= htmlspecialchars($course->credits) ?></span></td>
                            <td data-field="description"><span class="value"><?= htmlspecialchars($course->description ?? '') ?></span></td>

                            <?php if (!empty($isAdmin)): ?>
                            <td class="actions">
                                <a href="<?= htmlspecialchars($link->url('admin.editCourse', ['id' => $course->id])) ?>" class="btn btn-sm btn-secondary">Upraviť</a>
                                <form action="<?= htmlspecialchars($link->url('admin.deleteCourse')) ?>" method="post" onsubmit="return confirm('Naozaj chcete zmazať tento kurz?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($course->id) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Zmazať</button>
                                </form>
                            </td>
                            <?php elseif (!empty($isStudent)): ?>
                            <td class="actions">
                                <?php if ($alreadyEnrolled): ?>
                                    <?php if ($enrollmentStatus === 'approved'): ?>
                                        <button type="button" class="btn btn-sm btn-success" disabled>Zapísaný</button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-warning" disabled>Čaká na schválenie</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <form action="<?= htmlspecialchars($link->url('student.zapis')) ?>" method="post" class="d-inline">
                                        <input type="hidden" name="courseId" value="<?= htmlspecialchars($course->id) ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Zapísať sa</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
