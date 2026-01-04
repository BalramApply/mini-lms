<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'Student Details';

// Get student ID
if (!isset($_GET['id'])) {
    redirect('students.php');
}

$student_id = (int)$_GET['id'];

// Get student details
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND role = 'student'");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    redirect('students.php');
}

$student = mysqli_fetch_assoc($result);

// Get student enrollments
$enrollments_query = "
    SELECT e.*, c.title as course_title, c.duration, c.description
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY e.enrollment_date DESC
";
$stmt = mysqli_prepare($conn, $enrollments_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$enrollments = mysqli_stmt_get_result($stmt);

// Get statistics
$total_enrollments = mysqli_num_rows($enrollments);
mysqli_data_seek($enrollments, 0); // Reset pointer

$completed_count = 0;
$in_progress_count = 0;
$enrolled_count = 0;

$temp_enrollments = [];
while ($e = mysqli_fetch_assoc($enrollments)) {
    $temp_enrollments[] = $e;
    if ($e['completion_status'] == 'completed') $completed_count++;
    elseif ($e['completion_status'] == 'in_progress') $in_progress_count++;
    else $enrolled_count++;
}

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-0">
            <i class="bi bi-person-circle"></i> Student Details
        </h2>
        <p class="text-muted mb-0">View complete student information and progress</p>
    </div>
    
    <!-- Student Info Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> Student Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-person-circle text-primary" style="font-size: 5rem;"></i>
                    </div>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <?php if ($student['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Registered:</strong></td>
                            <td><?php echo format_date($student['created_at']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Student ID:</strong></td>
                            <td>#<?php echo $student['id']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="bi bi-journal-text text-primary" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2"><?php echo $total_enrollments; ?></h3>
                            <p class="text-muted mb-0">Total Enrollments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card warning">
                        <div class="card-body text-center">
                            <i class="bi bi-hourglass-split text-warning" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2"><?php echo $in_progress_count; ?></h3>
                            <p class="text-muted mb-0">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card success">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2"><?php echo $completed_count; ?></h3>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Chart -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Learning Progress</h6>
                    <?php
                    $completion_rate = $total_enrollments > 0 ? ($completed_count / $total_enrollments) * 100 : 0;
                    ?>
                    <div class="progress" style="height: 30px;">
                        <div 
                            class="progress-bar bg-success" 
                            role="progressbar" 
                            style="width: <?php echo $completion_rate; ?>%"
                            aria-valuenow="<?php echo $completion_rate; ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100"
                        >
                            <?php echo round($completion_rate, 1); ?>% Completed
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrolled Courses -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-bookmark-check"></i> Enrolled Courses
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($temp_enrollments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Duration</th>
                                <th>Enrolled On</th>
                                <th>Status</th>
                                <th>Completed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($temp_enrollments as $enrollment): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['course_title']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($enrollment['description'], 0, 50)) . '...'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock"></i>
                                        <?php echo htmlspecialchars($enrollment['duration']); ?>
                                    </td>
                                    <td><?php echo format_date($enrollment['enrollment_date']); ?></td>
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
                                            echo format_date($enrollment['completed_at']);
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>No enrollments yet</h5>
                    <p class="text-muted">This student hasn't enrolled in any courses</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="mt-3">
        <a href="students.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Students
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>