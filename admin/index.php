<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'Admin Dashboard';

// Get statistics
$total_courses = count_total_courses($conn);
$total_students = count_total_students($conn);
$total_enrollments = count_total_enrollments($conn);

// Get recent enrollments
$recent_enrollments_query = "
    SELECT e.*, u.name as student_name, c.title as course_title 
    FROM enrollments e 
    JOIN users u ON e.student_id = u.id 
    JOIN courses c ON e.course_id = c.id 
    ORDER BY e.enrollment_date DESC 
    LIMIT 5
";
$recent_enrollments = mysqli_query($conn, $recent_enrollments_query);

// Get course completion stats
$completion_stats_query = "
    SELECT 
        COUNT(CASE WHEN completion_status = 'enrolled' THEN 1 END) as enrolled_count,
        COUNT(CASE WHEN completion_status = 'in_progress' THEN 1 END) as in_progress_count,
        COUNT(CASE WHEN completion_status = 'completed' THEN 1 END) as completed_count
    FROM enrollments
";
$completion_stats = mysqli_fetch_assoc(mysqli_query($conn, $completion_stats_query));

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
            </h2>
            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
        <div>
            <span class="badge bg-primary">Admin Panel</span>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Courses -->
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Courses</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_courses; ?></h2>
                        </div>
                        <div class="stats-icon text-primary">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="courses.php" class="text-decoration-none small">
                            View all courses <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Students -->
        <div class="col-md-4">
            <div class="card stats-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Students</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_students; ?></h2>
                        </div>
                        <div class="stats-icon text-success">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="students.php" class="text-decoration-none small">
                            View all students <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Enrollments -->
        <div class="col-md-4">
            <div class="card stats-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Enrollments</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_enrollments; ?></h2>
                        </div>
                        <div class="stats-icon text-warning">
                            <i class="bi bi-bookmark-check"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="enrollments.php" class="text-decoration-none small">
                            View enrollments <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Completion Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up"></i> Course Completion Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <i class="bi bi-hourglass-split text-info" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="fw-bold"><?php echo $completion_stats['enrolled_count']; ?></h4>
                            <p class="text-muted mb-0">Enrolled</p>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <i class="bi bi-lightning-charge text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="fw-bold"><?php echo $completion_stats['in_progress_count']; ?></h4>
                            <p class="text-muted mb-0">In Progress</p>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="fw-bold"><?php echo $completion_stats['completed_count']; ?></h4>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Enrollments -->
    <div class="row g-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Recent Enrollments
                    </h5>
                    <a href="enrollments.php" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recent_enrollments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Course Title</th>
                                        <th>Enrollment Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($enrollment = mysqli_fetch_assoc($recent_enrollments)): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-person-circle"></i>
                                                <?php echo htmlspecialchars($enrollment['student_name']); ?>
                                            </td>
                                            <td>
                                                <i class="bi bi-book"></i>
                                                <?php echo htmlspecialchars($enrollment['course_title']); ?>
                                            </td>
                                            <td>
                                                <i class="bi bi-calendar"></i>
                                                <?php echo format_datetime($enrollment['enrollment_date']); ?>
                                            </td>
                                            <td>
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
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5>No enrollments yet</h5>
                            <p class="text-muted">Enrollments will appear here once students start enrolling</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4 mt-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="add_course.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add New Course
                        </a>
                        <a href="courses.php" class="btn btn-outline-primary">
                            <i class="bi bi-journal-text"></i> Manage Courses
                        </a>
                        <a href="students.php" class="btn btn-outline-success">
                            <i class="bi bi-people"></i> Manage Students
                        </a>
                        <a href="enrollments.php" class="btn btn-outline-warning">
                            <i class="bi bi-list-check"></i> View All Enrollments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>