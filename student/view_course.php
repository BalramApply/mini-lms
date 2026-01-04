<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is student
check_student();

$student_id = $_SESSION['user_id'];

// Get course ID
if (!isset($_GET['id'])) {
    redirect('my_courses.php');
}

$course_id = (int)$_GET['id'];

// Check if student is enrolled in this course
if (!is_enrolled($conn, $student_id, $course_id)) {
    $_SESSION['error'] = 'You must enroll in this course first.';
    redirect('courses.php');
}

// Get course details
$course_query = "SELECT c.*, e.completion_status, e.enrollment_date, e.completed_at 
                 FROM courses c 
                 JOIN enrollments e ON c.id = e.course_id 
                 WHERE c.id = ? AND e.student_id = ?";
$stmt = mysqli_prepare($conn, $course_query);
mysqli_stmt_bind_param($stmt, "ii", $course_id, $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    redirect('my_courses.php');
}

$course = mysqli_fetch_assoc($result);
$page_title = $course['title'];

$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'start') {
        $stmt = mysqli_prepare($conn, "UPDATE enrollments SET completion_status = 'in_progress' WHERE student_id = ? AND course_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $student_id, $course_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Course started! Keep learning.';
            $course['completion_status'] = 'in_progress';
        }
    } elseif ($action == 'complete') {
        $stmt = mysqli_prepare($conn, "UPDATE enrollments SET completion_status = 'completed', completed_at = NOW() WHERE student_id = ? AND course_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $student_id, $course_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Congratulations! You have completed this course!';
            $course['completion_status'] = 'completed';
            $course['completed_at'] = date('Y-m-d H:i:s');
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <!-- Alerts -->
    <?php if ($success): ?>
        <?php echo show_success($success); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo show_error($error); ?>
    <?php endif; ?>
    
    <!-- Course Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="bi bi-book"></i> <?php echo htmlspecialchars($course['title']); ?>
                    </h2>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-clock"></i> Duration: <?php echo htmlspecialchars($course['duration']); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-calendar"></i> Enrolled: <?php echo format_date($course['enrollment_date']); ?>
                        </span>
                        <?php
                        $status = $course['completion_status'];
                        $badge_class = 'bg-info';
                        if ($status == 'in_progress') {
                            $badge_class = 'bg-warning';
                        } elseif ($status == 'completed') {
                            $badge_class = 'bg-success';
                        }
                        ?>
                        <span class="badge <?php echo $badge_class; ?>">
                            Status: <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <?php if ($course['completion_status'] == 'completed'): ?>
                        <div class="mb-3">
                            <i class="bi bi-trophy-fill text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-success">Course Completed!</h5>
                        <small class="text-muted">Completed on: <?php echo format_date($course['completed_at']); ?></small>
                    <?php elseif ($course['completion_status'] == 'enrolled'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="start">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-circle"></i> Start Learning
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="" onsubmit="return confirm('Mark this course as completed?')">
                            <input type="hidden" name="action" value="complete">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Mark as Complete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Materials -->
    <div class="row g-4">
        <!-- Course Material (PDF) -->
        <?php if (!empty($course['course_material'])): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-pdf"></i> Course Material
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="material-icon mb-3">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h6><?php echo htmlspecialchars($course['course_material']); ?></h6>
                        <p class="text-muted">Study material for this course</p>
                        <a 
                            href="<?php echo UPLOAD_URL . $course['course_material']; ?>" 
                            class="btn btn-danger" 
                            download
                            target="_blank"
                        >
                            <i class="bi bi-download"></i> Download Material
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Video Tutorial -->
        <?php if (!empty($course['video_link'])): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-play-circle"></i> Video Tutorial
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="material-icon mb-3">
                            <i class="bi bi-youtube"></i>
                        </div>
                        <h6>Watch Video Lecture</h6>
                        <p class="text-muted">Learn through video content</p>
                        <a 
                            href="<?php echo htmlspecialchars($course['video_link']); ?>" 
                            class="btn btn-primary" 
                            target="_blank"
                        >
                            <i class="bi bi-play-circle"></i> Watch Now
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- If no materials available -->
        <?php if (empty($course['course_material']) && empty($course['video_link'])): ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5>No learning materials available yet</h5>
                            <p class="text-muted">Course materials will be uploaded soon</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Learning Tips -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-lightbulb"></i> Learning Tips
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Download and review the course material
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Watch the video tutorial carefully
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Take notes while learning
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Practice regularly for best results
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Mark as complete when you finish
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Review completed courses anytime
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="mt-3">
        <a href="my_courses.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to My Courses
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>