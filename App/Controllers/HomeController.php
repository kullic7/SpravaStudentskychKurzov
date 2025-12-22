<?php

namespace App\Controllers;

use App\Models\Enrollment;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;


class HomeController extends BaseController
{
    // Dashboard - shows different content depending on user's role
    public function index(Request $request): Response
    {
        //return $this->html([], 'login');
        $user = $this->app->getAuthenticator()->getUser();
        $role = strtolower($user->getRole());

        switch ($role) {
            case 'admin':
                // fetch counts from DB and pass to view
                $courseCount = Course::getCount();
                $userCount = User::getCount();
                $enrollmentCount = Enrollment::getPendingCount();

                return $this->html([
                    'courseCount' => $courseCount,
                    'userCount' => $userCount,
                    'enrollmentCount' => $enrollmentCount
                ], 'admin');
            case 'teacher':
                // Prepare teacher-specific stats: number of courses, students count, and per-course rows
                $totalCourses = 0;
                $studentsCount = 0;
                $teacherCourses = [];

                try {
                    $appUserId = $user->getId();
                    if ($appUserId !== null) {
                        $teacherModel = Teacher::findByUserId($appUserId);
                        if ($teacherModel !== null && $teacherModel->id !== null) {
                            $courses = Course::findByTeacherId($teacherModel->id);
                            $totalCourses = count($courses);

                            foreach ($courses as $c) {
                                // count approved students for this course
                                $studentCount = Enrollment::getCount('course_id = ? AND status = ?', [$c->id, 'approved']);
                                $studentsCount += $studentCount;

                                // compute average grade using Enrollment model helper (approved enrollments only)
                                $avg = Enrollment::averageGradeByCourse($c->id);

                                $teacherCourses[] = [
                                    'courseId' => $c->id,
                                    'name' => $c->name,
                                    'credits' => $c->credits,
                                    'studentCount' => $studentCount,
                                    'averageGrade' => $avg,
                                ];
                            }
                        }
                    }
                } catch (\Throwable $_) {
                    // keep defaults on error
                }

                return $this->html([
                    'totalCourses' => $totalCourses,
                    'studentsCount' => $studentsCount,
                    'teacherCourses' => $teacherCourses
                ], 'teacher');
             case 'student':
                 // compute student-specific stats: total courses, pending enrollments, average grade
                 $totalCourses = 0;
                 $pendingEnrollments = 0;
                 $averageGrade = null;
                 $studentEnrollments = [];

                 try {
                     $appUserId = $user->getId();
                     if ($appUserId !== null) {
                         $studentModel = Student::findByUserId($appUserId);
                         if ($studentModel !== null && $studentModel->id !== null) {
                             $totalCourses = Enrollment::countByStudent($studentModel->id);
                             $pendingEnrollments = Enrollment::pendingCountByStudent($studentModel->id);
                             $averageGrade = Enrollment::averageGradeByStudent($studentModel->id);

                             // load enrollments and related course info for the table
                             // only include already approved enrollments for the student's course table
                             $ens = Enrollment::getAll('student_id = ? AND status = ?', [$studentModel->id, 'approved']);
                              foreach ($ens as $e) {
                                  $course = $e->getCourse();
                                  // ensure teacher name is included per enrollment
                                  $studentEnrollments[] = [
                                      'courseId' => $course?->id ?? null,
                                      'courseName' => $course?->name ?? '-',
                                      'teacherName' => $course?->getTeacher()?->getUser()?->getName() ?? '-',
                                      'description' => $course?->description ?? null,
                                      'credits' => $course?->credits ?? null,
                                      'grade' => $e->grade ?? null,
                                  ];
                              }
                         }
                     }
                 } catch (\Throwable $_) {
                     // keep defaults on error
                 }

                 return $this->html([
                     'totalCourses' => $totalCourses,
                     'pendingEnrollments' => $pendingEnrollments,
                     'averageGrade' => $averageGrade,
                     'enrollments' => $studentEnrollments
                 ], 'student');
             default:
                 return $this->redirect('?c=auth&a=login');
        }
    }
}
