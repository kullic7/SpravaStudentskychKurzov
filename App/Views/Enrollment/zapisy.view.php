<?php
/** @var array $enrollments */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\LoggedUser|null $user */

$view->setLayout('home');
$title = 'Zápisy - moje / čakajúce';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zápisy</h1>

        <?php if (empty($enrollments)): ?>
            <p>Žiadne čakajúce zápisy neboli nájdené.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Študent</th>
                            <th>Email</th>
                            <th>Kurz</th>
                            <th>Stav</th>
                            <th>Akcia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $en):
                            $student = $en->getStudent();
                            $u = $student ? $student->getUser() : null;
                            $studentName = $u ? ($u->firstName . ' ' . $u->lastName) : '-';
                            $studentEmail = $u ? $u->email : '-';
                            $course = $en->getCourse();
                            $courseName = $course ? $course->name : '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($en->id) ?></td>
                            <td><?= htmlspecialchars($studentName) ?></td>
                            <td><?= htmlspecialchars($studentEmail) ?></td>
                            <td><?= htmlspecialchars($courseName) ?></td>
                            <td><?= htmlspecialchars($en->status) ?></td>
                            <td>
                                <?php if ($user && $user->getRole() === 'admin'): ?>
                                    <form method="post" action="<?= htmlspecialchars($link->url('admin.approveEnrollment')) ?>" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($en->id) ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Schváliť</button>
                                    </form>
                                <?php elseif ($user && $user->getRole() === 'student'): ?>
                                    <form method="post" action="<?= htmlspecialchars($link->url('student.cancelEnrollment')) ?>" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($en->id) ?>">
                                        <button type="submit" class="btn btn-sm btn-warning">Odhlásiť sa</button>
                                    </form>
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

