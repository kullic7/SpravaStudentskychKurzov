<?php
/** @var array $courses */
/** @var array $courseTeachers */
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
                            <th>Názov</th>
                            <th>Učiteľ</th>
                            <th>Email</th>
                            <th>Kredity</th>
                            <th>Popis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course):
                            // Use the precomputed courseTeachers map attached by the controller
                            $teachers = $courseTeachers[$course->id] ?? [];

                            if (empty($teachers)) {
                                $teacherNames = '-';
                                $teacherEmails = '-';
                            } else {
                                $names = [];
                                $emails = [];
                                foreach ($teachers as $tEntry) {
                                    // $tEntry is an object with keys: 'teacher', 'user', 'name', 'email'
                                    $name = $tEntry->name ?? null;
                                    $email = $tEntry->email ?? null;
                                    if ($name === null && !empty($tEntry->user)) {
                                        $u = $tEntry->user;
                                        $name = ($u->firstName ?? '') . ' ' . ($u->lastName ?? '');
                                    }
                                    if ($email === null && !empty($tEntry->user)) {
                                        $email = $tEntry->user->email ?? null;
                                    }
                                    $names[] = htmlspecialchars(trim($name) === '' ? '-' : $name);
                                    $emails[] = htmlspecialchars(trim($email) === '' ? '-' : $email);
                                }
                                $teacherNames = implode(', ', $names);
                                $teacherEmails = implode(', ', $emails);
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($course->name) ?></td>
                            <td><?= $teacherNames ?></td>
                            <td><?= $teacherEmails ?></td>
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
