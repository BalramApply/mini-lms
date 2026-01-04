<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is student
check_student();

$page_title = 'My Courses';

$student_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Check for session messages
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Get enrolled courses
$enrolled_courses_query = "
    SELECT e.*, c.title, c.description, c.duration, c.course_material, c.video_link
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY e.enrollment_date DESC
";
$stmt = mysqli_prepare($conn, $enrolled_courses_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$enrolled_courses = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-0">
            <i class="bi bi-bookmark-check"></i> My Enrolled Courses
        </h2>
        <p class="text-muted mb-0">Continue your learning journey</p>
    </div>
    
    <!-- Alerts -->
    <?php if ($success): ?>
        <?php echo show_success($success); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo show_error($error); ?>
    <?php endif; ?>
    
    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" id="courseTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                <i class="bi bi-list"></i> All Courses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="enrolled-tab" data-bs-toggle="tab" data-bs-target="#enrolled" type="button" role="tab">
                <i class="bi bi-hourglass-split"></i> Enrolled
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button" role="tab">
                <i class="bi bi-lightning-charge"></i> In Progress
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                <i class="bi bi-check-circle"></i> Completed
            </button>
        </li>
    </ul>
    
    <!-- Courses Content -->
    <?php if (mysqli_num_rows($enrolled_courses) > 0): ?>
        <div class="row g-4">
            <?php while ($enrollment = mysqli_fetch_assoc($enrolled_courses)): ?>
                <div class="col-md-6 col-lg-4" data-status="<?php echo $enrollment['completion_status']; ?>">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <?php
                            $status = $enrollment['completion_status'];
                            $badge_class = 'bg-info';
                            $badge_icon = 'hourglass-split';
                            $badge_text = 'Enrolled';
                            
                            if ($status == 'in_progress') {
                                $badge_class = 'bg-warning';
                                $badge_icon = 'lightning-charge';
                                $badge_text = 'In Progress';
                            } elseif ($status == 'completed') {
                                $badge_class = 'bg-success';
                                $badge_icon = 'check-circle';
                                $badge_text = 'Completed';
                            }
                            ?>
                            <span class="badge <?php echo $badge_class; ?> course-badge">
                                <i class="bi bi-<?php echo $badge_icon; ?>"></i> <?php echo $badge_text; ?>
                            </span>
                            
                            <h5 class="card-title mt-3">
                                <?php echo htmlspecialchars($enrollment['title']); ?>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars(substr($enrollment['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?php echo htmlspecialchars($enrollment['duration']); ?>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Enrolled: <?php echo format_date($enrollment['enrollment_date']); ?>
                                </small>
                            </div>
                            
                            <?php if ($enrollment['completed_at']): ?>
                                <div class="mb-3">
                                    <small class="text-success">
                                        <i class="bi bi-trophy"></i> Completed: <?php echo format_date($enrollment['completed_at']); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="view_course.php?id=<?php echo $enrollment['course_id']; ?>" class="btn btn-primary">
                                    <i class="bi bi-book-half"></i> 
                                    <?php echo ($status == 'completed') ? 'Review Course' : 'Continue Learning'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>No enrolled courses</h4>
                    <p class="text-muted">Start your learning journey by enrolling in a course</p>
                    <a href="courses.php" class="btn btn-primary mt-3">
                        <i class="bi bi-search"></i> Browse Courses
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Tab filtering
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
    
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.id;
            const courseCards = document.querySelectorAll('[data-status]');
            
            courseCards.forEach(card => {
                const status = card.getAttribute('data-status');
                
                if (targetId === 'all-tab') {
                    card.style.display = '';
                } else if (targetId === 'enrolled-tab' && status === 'enrolled') {
                    card.style.display = '';
                } else if (targetId === 'progress-tab' && status === 'in_progress') {
                    card.style.display = '';
                } else if (targetId === 'completed-tab' && status === 'completed') {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>