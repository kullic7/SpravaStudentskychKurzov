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
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($link->url('admin.createUser')) ?>" class="btn btn-sm btn-primary"> Vytvoriť používateľa</a>
        <?php if (empty($users)): ?>
            <p>Žiadni používatelia neboli nájdení.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped" id="usersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Meno</th>
                            <th>Priezvisko</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Ročník</th>
                            <th>Štud. číslo</th>
                            <th>Oddelenie</th>
                            <th>Akcie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user):
                            $firstName = htmlspecialchars($user->firstName ?? '-');
                            $lastName = htmlspecialchars($user->lastName ?? '-');
                            $email = htmlspecialchars($user->email ?? '-');
                            $role = htmlspecialchars($user->role ?? '-');

                            // load student/teacher rows (if exist)
                            $student = Student::findByUserId($user->id);
                            $teacher = Teacher::findByUserId($user->id);
                        ?>
                        <tr data-user-id="<?= htmlspecialchars($user->id) ?>" data-role="<?= htmlspecialchars($user->role ?? '') ?>">
                            <td><?= htmlspecialchars($user->id) ?></td>
                            <td data-col="firstName"><span class="value"><?= $firstName ?></span></td>
                            <td data-col="lastName"><span class="value"><?= $lastName ?></span></td>
                            <td data-col="email"><span class="value"><?= $email ?></span></td>
                            <td data-col="role"><span class="value"><?= $role ?></span></td>
                            <td data-col="year"><span class="value"><?= $student ? htmlspecialchars($student->year ?? '-') : '-' ?></span></td>
                            <td data-col="studentNumber"><span class="value"><?= $student ? htmlspecialchars($student->studentNumber ?? '-') : '-' ?></span></td>
                            <td data-col="department"><span class="value"><?= $teacher ? htmlspecialchars($teacher->department ?? '-') : '-' ?></span></td>
                            <td class="actions">
                                <a href="<?= htmlspecialchars($link->url('admin.editUser', ['id' => $user->id])) ?>" class="btn btn-sm btn-primary">Upraviť</a>
                                <form action="<?= htmlspecialchars($link->url('admin.deleteUser')) ?>"
                                      method="post"
                                      class="d-inline-block"
                                      onsubmit="return confirm('Naozaj chcete zmazať tohto používateľa?');">

                                    <input type="hidden" name="id" value="<?= htmlspecialchars($user->id) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Zmazať
                                    </button>
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
