<?php
/** @var \Framework\Support\View $view */
/** @var \Framework\Auth\AppUser $user */
// Set the layout to the main root layout
use App\Configuration;

$view->setLayout(Configuration::HOME_LAYOUT);

$totalCourses = $totalCourses ?? 0;
$pendingEnrollments = $pendingEnrollments ?? 0;
$averageGrade = $averageGrade ?? null;

?>

<div class="mt-5 pt-4 text-center">
        <h1>Vitajte, <?= htmlspecialchars($user->getName() ?? 'Hosť') ?></h1>
</div>

<div  class="row justify-content-center mt-5">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="rounded p-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Počet predmetov</h5>
                <p class="card-text display-6"><?= htmlspecialchars((string)$totalCourses) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="rounded p-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Čakajúce zápisy</h5>
                <p class="card-text display-6"><?= htmlspecialchars((string)$pendingEnrollments) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="rounded p-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Priemerná známka</h5>
                <p class="card-text display-6"><?= $averageGrade === null ? '-' : htmlspecialchars(number_format((float)$averageGrade, 2)) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($enrollments) || !is_array($enrollments)): ?>
            <p>Nie sú nájdené žiadne zapisné predmety.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped" id="coursesTable">
                    <thead>
                        <tr>
                            <th>Názov</th>
                            <th>Učiteľ</th>
                            <th>Popis</th>
                            <th>Kredity</th>
                            <th>Známka</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $row):
                            $cname = $row['courseName'] ?? '-';
                            $cteacherName = $row['teacherName'] ?? '-';
                            $cdesc = $row['description'] ?? null;
                            $credits = $row['credits'] !== null ? (string)$row['credits'] : null;
                            $grade = $row['grade'] !== null ? (string)$row['grade'] : null;
                        ?>
                        <tr>
                            <td data-col="name"><span class="value"><?= htmlspecialchars($cname) ?></span></td>
                            <td data-col="teacher"><span class="value"><?= htmlspecialchars($cteacherName) ?></span></td>
                            <td data-col="description"><span class="value"><?= htmlspecialchars($cdesc ?? '-') ?></span></td>
                            <td data-col="credits"><span class="value"><?= $credits !== null ? htmlspecialchars($credits) : '-' ?></span></td>
                            <td data-col="grade"><span class="value"><?= $grade !== null ? htmlspecialchars($grade) : '-' ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
