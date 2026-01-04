<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is student
check_student();

$student_id = $_SESSION['user_id'];

// Get course ID
if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$course_id = (int)$_GET['id'];

// Check if course exists and is active
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Course not found or is not available.';
    redirect('courses.php');
}

// Check if already enrolled
if (is_enrolled($conn, $student_id, $course_id)) {
    $_SESSION['error'] = 'You are already enrolled in this course.';
    redirect('my_courses.php');
}

// Enroll student
$stmt = mysqli_prepare($conn, "INSERT INTO enrollments (student_id, course_id, enrollment_date, completion_status) VALUES (?, ?, NOW(), 'enrolled')");
mysqli_stmt_bind_param($stmt, "ii", $student_id, $course_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Successfully enrolled in the course!';
    redirect('my_courses.php');
} else {
    $_SESSION['error'] = 'Failed to enroll in the course. Please try again.';
    redirect('courses.php');
}
?>