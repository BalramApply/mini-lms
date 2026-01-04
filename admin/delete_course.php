<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

// Get course ID
if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$course_id = (int)$_GET['id'];

// Get course details
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    redirect('courses.php');
}

$course = mysqli_fetch_assoc($result);

// Delete course material file if exists
if (!empty($course['course_material']) && file_exists(UPLOAD_DIR . $course['course_material'])) {
    unlink(UPLOAD_DIR . $course['course_material']);
}

// Delete course (this will also delete related enrollments due to CASCADE)
$stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $course_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['delete_success'] = 'Course deleted successfully!';
} else {
    $_SESSION['delete_error'] = 'Failed to delete course.';
}

redirect('courses.php');
?>