<?php
/** @var \Framework\Support\View $view */
/** @var \Framework\Auth\AppUser $user */
/** @var int $totalCourses */
/** @var int $studentsCount */
/** @var array $teacherCourses */

$view->setLayout('home');

$totalCourses = $totalCourses ?? 0;
$studentsCount = $studentsCount ?? 0;
$teacherCourses = $teacherCourses ?? [];
?>

<div class="mt-5 pt-4 text-center">
    <h1>Vitajte, <?= htmlspecialchars($user->getName() ?? 'Hosť') ?></h1>
</div>

<div  class="row justify-content-center mt-5">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="border p-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Počet mojich kurzov</h5>
                <p class="card-text display-6"><?= htmlspecialchars((string)$totalCourses) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="border p-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Počet študentov (schválené)</h5>
                <p class="card-text display-6"><?= htmlspecialchars((string)$studentsCount) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <?php if (empty($teacherCourses) || !is_array($teacherCourses)): ?>
            <p>Nie sú nájdené žiadne kurzy, ktoré by ste vyučovali.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped" id="teacherCoursesTable">
                    <thead>
                        <tr>
                            <th>Názov kurzu</th>
                            <th>Kredity</th>
                            <th>Počet študentov</th>
                            <th>Priemerná známka</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teacherCourses as $row):
                            $cname = $row['name'] ?? '-';
                            $credits = $row['credits'] !== null ? (string)$row['credits'] : '-';
                            $studentCount = isset($row['studentCount']) ? (string)$row['studentCount'] : '0';
                            $avg = array_key_exists('averageGrade', $row) && $row['averageGrade'] !== null ? number_format((float)$row['averageGrade'], 2) : '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($cname) ?></td>
                            <td><?= htmlspecialchars($credits) ?></td>
                            <td><?= htmlspecialchars($studentCount) ?></td>
                            <td><?= $avg !== '-' ? htmlspecialchars($avg) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
