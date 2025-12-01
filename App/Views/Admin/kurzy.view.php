<?php
/** @var array $courses */
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

        <?php if (empty($courses)): ?>
            <p>Žiadne kurzy neboli nájdené.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Názov</th>
                            <th>Učiteľ</th>
                            <th>Email</th>
                            <th>Kredity</th>
                            <th>Popis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course):
                            $t = $course->getTeacher();
                            $u = $t ? $t->getUser() : null;
                            $teacherName = $u ? ($u->firstName . ' ' . $u->lastName) : '-';
                            $teacherEmail = $u ? $u->email : '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($course->id) ?></td>
                            <td><?= htmlspecialchars($course->name) ?></td>
                            <td><?= htmlspecialchars($teacherName) ?></td>
                            <td><?= htmlspecialchars($teacherEmail) ?></td>
                            <td><?= htmlspecialchars($course->credits) ?></td>
                            <td><?= htmlspecialchars($course->description) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

