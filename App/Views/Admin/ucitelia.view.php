<?php
/** @var array $teachers */
/** @var \Framework\Support\View $view */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\LoggedUser|null $user */

// Use the home layout for this view
$view->setLayout('home');

// Page title
$title = 'Učitelia';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zoznam učiteľov</h1>

        <?php if (empty($teachers)): ?>
            <p>Žiadni učitelia neboli nájdení.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Meno</th>
                        <th>Email</th>
                        <th>Oddelenie</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($teachers as $teacher):
                        $u = $teacher->getUser();
                        $name = $u ? ($u->firstName . ' ' . $u->lastName) : '-';
                        $email = $u ? $u->email : '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($teacher->id) ?></td>
                            <td><?= htmlspecialchars($name) ?></td>
                            <td><?= htmlspecialchars($email) ?></td>
                            <td><?= htmlspecialchars($teacher->department) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>