<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'Manage Courses';

$success = '';
$error = '';

// Check for delete messages
if (isset($_SESSION['delete_success'])) {
    $success = $_SESSION['delete_success'];
    unset($_SESSION['delete_success']);
}

if (isset($_SESSION['delete_error'])) {
    $error = $_SESSION['delete_error'];
    unset($_SESSION['delete_error']);
}

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $course_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'activate' || $action == 'deactivate') {
        $new_status = ($action == 'activate') ? 'active' : 'inactive';
        $stmt = mysqli_prepare($conn, "UPDATE courses SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $course_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Course " . ($action == 'activate' ? 'activated' : 'deactivated') . " successfully!";
        } else {
            $error = "Failed to update course status.";
        }
    }
}

// Get all courses
$courses_query = "
    SELECT c.*, u.name as creator_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
    FROM courses c
    LEFT JOIN users u ON c.created_by = u.id
    ORDER BY c.created_at DESC
";
$courses = mysqli_query($conn, $courses_query);

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-journal-text"></i> Manage Courses
            </h2>
            <p class="text-muted mb-0">Create, update, and manage all courses</p>
        </div>
        <div>
            <a href="add_course.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Course
            </a>
        </div>
    </div>
    
    <!-- Alerts -->
    <?php if ($success): ?>
        <?php echo show_success($success); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo show_error($error); ?>
    <?php endif; ?>
    
    <!-- Courses Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> All Courses
            </h5>
        </div>
        <div class="card-body">
            <!-- Search Box -->
            <div class="mb-3">
                <input 
                    type="text" 
                    class="form-control" 
                    id="searchInput" 
                    placeholder="Search courses by title or description..."
                    onkeyup="searchTable('searchInput', 'coursesTable')"
                >
            </div>
            
            <?php if (mysqli_num_rows($courses) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="coursesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                                <tr>
                                    <td><?php echo $course['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($course['description'], 0, 60)) . '...'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock"></i>
                                        <?php echo htmlspecialchars($course['duration']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $course['enrollment_count']; ?> students
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($course['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($course['creator_name']); ?></td>
                                    <td><?php echo format_date($course['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a 
                                                href="edit_course.php?id=<?php echo $course['id']; ?>" 
                                                class="btn btn-sm btn-warning btn-action"
                                                title="Edit Course"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <?php if ($course['status'] == 'active'): ?>
                                                <a 
                                                    href="?action=deactivate&id=<?php echo $course['id']; ?>" 
                                                    class="btn btn-sm btn-secondary btn-action"
                                                    title="Deactivate Course"
                                                    onclick="return confirm('Deactivate this course?')"
                                                >
                                                    <i class="bi bi-pause-circle"></i>
                                                </a>
                                            <?php else: ?>
                                                <a 
                                                    href="?action=activate&id=<?php echo $course['id']; ?>" 
                                                    class="btn btn-sm btn-success btn-action"
                                                    title="Activate Course"
                                                    onclick="return confirm('Activate this course?')"
                                                >
                                                    <i class="bi bi-play-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a 
                                                href="delete_course.php?id=<?php echo $course['id']; ?>" 
                                                class="btn btn-sm btn-danger btn-action"
                                                title="Delete Course"
                                                onclick="return confirmDelete('<?php echo htmlspecialchars($course['title']); ?>')"
                                            >
                                                <i class="bi bi-trash"></i>
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
                    <i class="bi bi-inbox"></i>
                    <h4>No courses found</h4>
                    <p class="text-muted">Start by adding your first course</p>
                    <a href="add_course.php" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Add Course
                    </a>
                </div>
            <?php endif; ?>
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