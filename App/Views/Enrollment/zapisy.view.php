<?php
/** @var array $rows */
/** @var \App\Models\LoggedUser|null $user */
/** @var \Framework\Support\View $view */
/** @var \Framework\Support\LinkGenerator $link */

$view->setLayout('home');
$title = 'Zápisy - moje / čakajúce';
?>

<div class="card">
    <div class="card-body">
        <h1 class="h3 mb-4">Zápisy</h1>

        <?php if (empty($rows)): ?>
            <p>Žiadne čakajúce zápisy neboli nájdené.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Študent</th>
                        <th>Email</th>
                        <th>Kurz</th>
                        <th>Stav</th>
                        <th>Akcia</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['studentName']) ?></td>
                            <td><?= htmlspecialchars($row['studentEmail']) ?></td>
                            <td><?= htmlspecialchars($row['courseName']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <?php if ($user && $user->getRole() === 'admin'): ?>
                                    <form method="post"
                                          action="<?= htmlspecialchars($link->url('admin.approveEnrollment')) ?>"
                                          class="d-inline">
                                        <input type="hidden" name="id"
                                               value="<?= htmlspecialchars($row['id']) ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-success">
                                            Schváliť
                                        </button>
                                    </form>
                                <?php elseif ($user && $user->getRole() === 'student'): ?>
                                    <form method="post"
                                          action="<?= htmlspecialchars($link->url('student.cancelEnrollment')) ?>"
                                          class="d-inline">
                                        <input type="hidden" name="id"
                                               value="<?= htmlspecialchars($row['id']) ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-warning">
                                            Odhlásiť sa
                                        </button>
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
