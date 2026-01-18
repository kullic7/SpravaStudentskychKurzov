<?php
/** @var array $students */
/** @var \App\Models\Teacher $teacher */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');
$title = 'Študenti';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Študenti učiteľa <?= htmlspecialchars($teacher?->getUser()?->getName() ?? ($teacher?->department ?? '-')) ?></h1>

        <?php if (empty($students)): ?>
            <p>Žiadni študenti nenájdení.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Meno</th>
                            <th>Priezvisko</th>
                            <th>Email</th>
                            <th>Štud. číslo</th>
                            <th>Ročník</th>
                            <th>Kurzy (známka)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $entry):
                            $student = $entry['student'] ?? null;
                            $user = $entry['user'] ?? null;
                            $courses = $entry['courses'] ?? [];
                            $firstName = $user?->firstName ?? '-';
                            $lastName = $user?->lastName ?? '-';
                            $email = $user?->email ?? '-';
                            $studentNumber = $student?->studentNumber ?? '-';
                            $year = $student?->year ?? '-';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($firstName) ?></td>
                                <td><?= htmlspecialchars($lastName) ?></td>
                                <td><?= htmlspecialchars($email) ?></td>
                                <td><?= htmlspecialchars($studentNumber) ?></td>
                                <td><?= htmlspecialchars($year) ?></td>
                                <td>
                                    <?php if (empty($courses)): ?>
                                        -
                                    <?php else: ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($courses as $cdata):
                                                $course = $cdata['course'] ?? null;
                                                $enrollment = $cdata['enrollment'] ?? null;
                                                $cname = $course?->name ?? '-';
                                                $grade = $enrollment?->grade ?? null;
                                                $gradeDisplay = ($grade === null || $grade === '') ? '-' : $grade;
                                                $enId = $enrollment?->id ?? null;
                                            ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="me-2"><strong><?= htmlspecialchars($cname) ?></strong></span>
                                                    <form action="<?= htmlspecialchars($link->url('teacher.updateGrade')) ?>" method="post" class="d-inline-flex align-items-center grade-ajax-form">
                                                        <input type="hidden" name="enrollmentId" value="<?= htmlspecialchars($enId) ?>">
                                                        <input type="text" name="grade" pattern="^(A|a|b|c|d|e|B|C|D|E|Fx)$"
                                                               title="Povolené známky: A–E alebo Fx" value="<?= htmlspecialchars($gradeDisplay === '-' ? '' : $gradeDisplay) ?>" placeholder="-" class="form-control form-control-sm grade-input" style="width:100px;" />
                                                        <button type="submit" class="btn btn-sm btn-primary ms-2">Uložiť</button>
                                                        <span class="ms-2 text-success small save-status" style="display:none;">Uložené</span>
                                                        <span class="ms-2 text-danger small save-error" style="display:none;"></span>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="<?= $link->asset('js/ajaxEditGradeScript.js') ?>"></script>

