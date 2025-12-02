<?php
/** @var array $users */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

use App\Models\Student;
use App\Models\Teacher;

$view->setLayout('home');

$title = 'Používatelia';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zoznam používateľov</h1>

        <?php if (empty($users)): ?>
            <p>Žiadni používatelia neboli nájdení.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Meno</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Štud. číslo</th>
                            <th>Oddelenie</th>
                            <th>Akcie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user):
                            $name = htmlspecialchars(($user->firstName ?? '') . ' ' . ($user->lastName ?? ''));
                            $email = htmlspecialchars($user->email ?? '-');
                            $role = htmlspecialchars($user->role ?? '-');

                            // try to load student/teacher rows (if exist)
                            $studentRows = Student::getAll('user_id = ?', [$user->id], null, 1);
                            $student = $studentRows[0] ?? null;

                            $teacher = Teacher::findByUserId($user->id);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($user->id) ?></td>
                            <td><?= $name ?></td>
                            <td><?= $email ?></td>
                            <td><?= $role ?></td>
                            <td><?= $student ? htmlspecialchars($student->studentNumber) : '-' ?></td>
                            <td><?= $teacher ? htmlspecialchars($teacher->department) : '-' ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($link->url('admin.editUser', ['id' => $user->id])) ?>" class="btn btn-sm btn-primary">Upraviť</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
