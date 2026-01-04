<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'View Enrollments';

// Get all enrollments with student and course details
$enrollments_query = "
    SELECT 
        e.*,
        u.name as student_name,
        u.email as student_email,
        c.title as course_title,
        c.duration
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrollment_date DESC
";
$enrollments = mysqli_query($conn, $enrollments_query);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN completion_status = 'enrolled' THEN 1 END) as enrolled,
        COUNT(CASE WHEN completion_status = 'in_progress' THEN 1 END) as in_progress,
        COUNT(CASE WHEN completion_status = 'completed' THEN 1 END) as completed
    FROM enrollments
";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-0">
            <i class="bi bi-bookmark-check"></i> All Enrollments
        </h2>
        <p class="text-muted mb-0">Track all student enrollments and their progress</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-list-check text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo $stats['total']; ?></h3>
                    <p class="text-muted mb-0">Total Enrollments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card info">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo $stats['enrolled']; ?></h3>
                    <p class="text-muted mb-0">Enrolled</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card warning">
                <div class="card-body text-center">
                    <i class="bi bi-lightning-charge text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo $stats['in_progress']; ?></h3>
                    <p class="text-muted mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo $stats['completed']; ?></h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollments Table -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> Enrollment Records
            </h5>
            <button class="btn btn-light btn-sm" onclick="printPage()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <input 
                        type="text" 
                        class="form-control" 
                        id="searchInput" 
                        placeholder="Search by student name, email, or course..."
                        onkeyup="searchTable('searchInput', 'enrollmentsTable')"
                    >
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="statusFilter" onchange="filterByStatus()">
                        <option value="">All Status</option>
                        <option value="enrolled">Enrolled</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($enrollments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="enrollmentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Duration</th>
                                <th>Enrolled On</th>
                                <th>Status</th>
                                <th>Completed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($enrollment = mysqli_fetch_assoc($enrollments)): ?>
                                <tr data-status="<?php echo $enrollment['completion_status']; ?>">
                                    <td><?php echo $enrollment['id']; ?></td>
                                    <td>
                                        <i class="bi bi-person-circle text-primary"></i>
                                        <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($enrollment['student_email']); ?></small>
                                    </td>
                                    <td>
                                        <i class="bi bi-book"></i>
                                        <?php echo htmlspecialchars($enrollment['course_title']); ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock"></i>
                                        <?php echo htmlspecialchars($enrollment['duration']); ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar"></i>
                                        <?php echo format_datetime($enrollment['enrollment_date']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $enrollment['completion_status'];
                                        $badge_class = 'bg-info';
                                        $icon = 'hourglass-split';
                                        if ($status == 'in_progress') {
                                            $badge_class = 'bg-warning';
                                            $icon = 'lightning-charge';
                                        } elseif ($status == 'completed') {
                                            $badge_class = 'bg-success';
                                            $icon = 'check-circle';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <i class="bi bi-<?php echo $icon; ?>"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($enrollment['completed_at']) {
                                            echo '<i class="bi bi-check-circle text-success"></i> ';
                                            echo format_datetime($enrollment['completed_at']);
                                        } else {
                                            echo '<span class="text-muted">Not completed</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>No enrollments found</h4>
                    <p class="text-muted">Enrollments will appear here once students start enrolling</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Summary Card -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-graph-up"></i> Enrollment Summary
            </h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h6 class="text-muted">Completion Rate</h6>
                    <h3 class="text-success">
                        <?php 
                        $completion_rate = $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0;
                        echo round($completion_rate, 1) . '%';
                        ?>
                    </h3>
                    <div class="progress mt-2">
                        <div 
                            class="progress-bar bg-success" 
                            role="progressbar" 
                            style="width: <?php echo $completion_rate; ?>%"
                        ></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Active Learning</h6>
                    <h3 class="text-warning">
                        <?php 
                        $active_rate = $stats['total'] > 0 ? ($stats['in_progress'] / $stats['total']) * 100 : 0;
                        echo round($active_rate, 1) . '%';
                        ?>
                    </h3>
                    <div class="progress mt-2">
                        <div 
                            class="progress-bar bg-warning" 
                            role="progressbar" 
                            style="width: <?php echo $active_rate; ?>%"
                        ></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Just Enrolled</h6>
                    <h3 class="text-info">
                        <?php 
                        $enrolled_rate = $stats['total'] > 0 ? ($stats['enrolled'] / $stats['total']) * 100 : 0;
                        echo round($enrolled_rate, 1) . '%';
                        ?>
                    </h3>
                    <div class="progress mt-2">
                        <div 
                            class="progress-bar bg-info" 
                            role="progressbar" 
                            style="width: <?php echo $enrolled_rate; ?>%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back to Dashboard -->
    <div class="mt-3">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<script>
// Filter enrollments by status
function filterByStatus() {
    const filter = document.getElementById('statusFilter').value.toLowerCase();
    const table = document.getElementById('enrollmentsTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const status = rows[i].getAttribute('data-status');
        
        if (filter === '' || status === filter) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>