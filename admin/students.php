<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'Manage Students';

$success = '';
$error = '';

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $student_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'activate' || $action == 'deactivate') {
        $new_status = ($action == 'activate') ? 'active' : 'inactive';
        $stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ? AND role = 'student'");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $student_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Student account " . ($action == 'activate' ? 'activated' : 'deactivated') . " successfully!";
        } else {
            $error = "Failed to update student status.";
        }
    }
}

// Get all students with enrollment count
$students_query = "
    SELECT u.*, 
    (SELECT COUNT(*) FROM enrollments WHERE student_id = u.id) as enrollment_count,
    (SELECT COUNT(*) FROM enrollments WHERE student_id = u.id AND completion_status = 'completed') as completed_count
    FROM users u
    WHERE u.role = 'student'
    ORDER BY u.created_at DESC
";
$students = mysqli_query($conn, $students_query);

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-people"></i> Manage Students
            </h2>
            <p class="text-muted mb-0">View and manage all registered students</p>
        </div>
        <div>
            <span class="badge bg-success fs-6">
                <?php echo mysqli_num_rows($students); ?> Total Students
            </span>
        </div>
    </div>
    
    <!-- Alerts -->
    <?php if ($success): ?>
        <?php echo show_success($success); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo show_error($error); ?>
    <?php endif; ?>
    
    <!-- Students Table -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> All Students
            </h5>
        </div>
        <div class="card-body">
            <!-- Search Box -->
            <div class="mb-3">
                <input 
                    type="text" 
                    class="form-control" 
                    id="searchInput" 
                    placeholder="Search students by name or email..."
                    onkeyup="searchTable('searchInput', 'studentsTable')"
                >
            </div>
            
            <?php if (mysqli_num_rows($students) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="studentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Enrollments</th>
                                <th>Completed</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = mysqli_fetch_assoc($students)): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td>
                                        <i class="bi bi-person-circle text-primary"></i>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-envelope"></i>
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $student['enrollment_count']; ?> courses
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo $student['completed_count']; ?> completed
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($student['status'] == 'active'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar"></i>
                                        <?php echo format_date($student['created_at']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($student['status'] == 'active'): ?>
                                                <a 
                                                    href="?action=deactivate&id=<?php echo $student['id']; ?>" 
                                                    class="btn btn-sm btn-danger btn-action"
                                                    title="Deactivate Account"
                                                    onclick="return confirm('Deactivate this student account? They will not be able to login.')"
                                                >
                                                    <i class="bi bi-x-circle"></i> Deactivate
                                                </a>
                                            <?php else: ?>
                                                <a 
                                                    href="?action=activate&id=<?php echo $student['id']; ?>" 
                                                    class="btn btn-sm btn-success btn-action"
                                                    title="Activate Account"
                                                    onclick="return confirm('Activate this student account?')"
                                                >
                                                    <i class="bi bi-check-circle"></i> Activate
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a 
                                                href="student_details.php?id=<?php echo $student['id']; ?>" 
                                                class="btn btn-sm btn-primary btn-action"
                                                title="View Details"
                                            >
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h4>No students found</h4>
                    <p class="text-muted">Students will appear here once they register</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Active Students</h6>
                    <h3 class="text-success">
                        <?php
                        $active_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'active'"));
                        echo $active_students['count'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Inactive Students</h6>
                    <h3 class="text-danger">
                        <?php
                        $inactive_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'inactive'"));
                        echo $inactive_students['count'];
                        ?>
                    </h3>
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

<?php include '../includes/footer.php'; ?>