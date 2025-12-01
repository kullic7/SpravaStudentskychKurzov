<?php
/** @var array $students */
/** @var \Framework\Support\View $view */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\LoggedUser|null $user */

// Use the home layout for this view
$view->setLayout('home');

// Page title
$title = 'Študenti';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zoznam študentov</h1>

        <?php if (empty($students)): ?>
            <p>Žiadni študenti neboli nájdení.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Meno</th>
                            <th>Email</th>
                            <th>Štud. číslo</th>
                            <th>Ročník</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student):
                            $u = $student->getUser();
                            $name = $u ? ($u->firstName . ' ' . $u->lastName) : '-';
                            $email = $u ? $u->email : '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($student->id) ?></td>
                            <td><?= htmlspecialchars($name) ?></td>
                            <td><?= htmlspecialchars($email) ?></td>
                            <td><?= htmlspecialchars($student->studentNumber) ?></td>
                            <td><?= htmlspecialchars($student->year) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

