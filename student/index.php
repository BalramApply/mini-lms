<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is student
check_student();

$page_title = 'Student Dashboard';

$student_id = $_SESSION['user_id'];

// Get enrolled courses count
$enrolled_count_query = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ?";
$stmt = mysqli_prepare($conn, $enrolled_count_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$enrolled_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];

// Get completed courses count
$completed_count_query = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND completion_status = 'completed'";
$stmt = mysqli_prepare($conn, $completed_count_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$completed_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];

// Get in-progress courses count
$in_progress_count_query = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND completion_status = 'in_progress'";
$stmt = mysqli_prepare($conn, $in_progress_count_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$in_progress_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];

// Get total available courses
$total_courses = count_total_courses($conn);

// Get recent enrollments
$recent_enrollments_query = "
    SELECT e.*, c.title, c.description, c.duration
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY e.enrollment_date DESC
    LIMIT 4
";
$stmt = mysqli_prepare($conn, $recent_enrollments_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$recent_enrollments = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-0">
            <i class="bi bi-speedometer2"></i> My Dashboard
        </h2>
        <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! Track your learning progress.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Available Courses -->
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Available Courses</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_courses; ?></h2>
                        </div>
                        <div class="stats-icon text-primary">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="courses.php" class="text-decoration-none small">
                            Browse courses <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enrolled Courses -->
        <div class="col-md-3">
            <div class="card stats-card info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">My Courses</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $enrolled_count; ?></h2>
                        </div>
                        <div class="stats-icon text-info">
                            <i class="bi bi-bookmark-check"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="my_courses.php" class="text-decoration-none small">
                            View my courses <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- In Progress -->
        <div class="col-md-3">
            <div class="card stats-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">In Progress</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $in_progress_count; ?></h2>
                        </div>
                        <div class="stats-icon text-warning">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="small text-muted">Keep learning!</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Completed Courses -->
        <div class="col-md-3">
            <div class="card stats-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Completed</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $completed_count; ?></h2>
                        </div>
                        <div class="stats-icon text-success">
                            <i class="bi bi-award"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="small text-muted">Great job!</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Progress Overview -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-graph-up"></i> Learning Progress
            </h5>
        </div>
        <div class="card-body">
            <?php
            $completion_rate = $enrolled_count > 0 ? ($completed_count / $enrolled_count) * 100 : 0;
            ?>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-3">Overall Completion Rate</h6>
                    <div class="progress" style="height: 30px;">
                        <div 
                            class="progress-bar bg-success" 
                            role="progressbar" 
                            style="width: <?php echo $completion_rate; ?>%"
                            aria-valuenow="<?php echo $completion_rate; ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100"
                        >
                            <?php echo round($completion_rate, 1); ?>%
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <h3 class="text-success mb-0"><?php echo $completed_count; ?> / <?php echo $enrolled_count; ?></h3>
                    <p class="text-muted mb-0">Courses Completed</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Enrollments / Continue Learning -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-book"></i> Continue Learning
            </h5>
            <a href="my_courses.php" class="btn btn-light btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($recent_enrollments) > 0): ?>
                <div class="row g-3">
                    <?php while ($enrollment = mysqli_fetch_assoc($recent_enrollments)): ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($enrollment['title']); ?></h6>
                                        <?php
                                        $status = $enrollment['completion_status'];
                                        $badge_class = 'bg-info';
                                        if ($status == 'in_progress') {
                                            $badge_class = 'bg-warning';
                                        } elseif ($status == 'completed') {
                                            $badge_class = 'bg-success';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        <?php echo htmlspecialchars(substr($enrollment['description'], 0, 80)) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted">
                                            <i class="bi bi-clock"></i> <?php echo htmlspecialchars($enrollment['duration']); ?>
                                        </span>
                                        <a href="view_course.php?id=<?php echo $enrollment['course_id']; ?>" class="btn btn-primary btn-sm">
                                            <?php echo ($status == 'completed') ? 'Review' : 'Continue'; ?>
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-book"></i>
                    <h5>No courses enrolled yet</h5>
                    <p class="text-muted">Start your learning journey by enrolling in a course</p>
                    <a href="courses.php" class="btn btn-primary mt-3">
                        <i class="bi bi-search"></i> Browse Courses
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-lightning"></i> Quick Actions
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="courses.php" class="btn btn-primary">
                    <i class="bi bi-search"></i> Browse All Courses
                </a>
                <a href="my_courses.php" class="btn btn-outline-primary">
                    <i class="bi bi-bookmark-check"></i> My Enrolled Courses
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>