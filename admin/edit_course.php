<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'Edit Course';

$error = '';
$success = '';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $duration = clean_input($_POST['duration']);
    $video_link = clean_input($_POST['video_link']);
    $status = clean_input($_POST['status']);
    
    // Validation
    if (empty($title) || empty($description) || empty($duration)) {
        $error = 'Please fill in all required fields';
    } else {
        $course_material = $course['course_material'];
        
        // Handle file upload
        if (isset($_FILES['course_material']) && $_FILES['course_material']['error'] == 0) {
            $upload_result = upload_file($_FILES['course_material']);
            
            if ($upload_result['success']) {
                // Delete old file if exists
                if (!empty($course['course_material']) && file_exists(UPLOAD_DIR . $course['course_material'])) {
                    unlink(UPLOAD_DIR . $course['course_material']);
                }
                $course_material = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        // If no error, update course
        if (empty($error)) {
            $stmt = mysqli_prepare($conn, "UPDATE courses SET title = ?, description = ?, duration = ?, course_material = ?, video_link = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $description, $duration, $course_material, $video_link, $status, $course_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Course updated successfully!';
                // Refresh course data
                $stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $course_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $course = mysqli_fetch_assoc($result);
            } else {
                $error = 'Failed to update course. Please try again.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-0">
            <i class="bi bi-pencil"></i> Edit Course
        </h2>
        <p class="text-muted mb-0">Update course information</p>
    </div>
    
    <!-- Alerts -->
    <?php if ($success): ?>
        <?php echo show_success($success); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo show_error($error); ?>
    <?php endif; ?>
    
    <!-- Edit Course Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text"></i> Course Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="bi bi-card-heading"></i> Course Title <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                value="<?php echo htmlspecialchars($course['title']); ?>"
                                required
                            >
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="bi bi-card-text"></i> Course Description <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="description" 
                                name="description" 
                                rows="5" 
                                required
                            ><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">
                                        <i class="bi bi-clock"></i> Course Duration <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="duration" 
                                        name="duration" 
                                        value="<?php echo htmlspecialchars($course['duration']); ?>"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        <i class="bi bi-toggle-on"></i> Course Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?php echo ($course['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($course['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="course_material" class="form-label">
                                <i class="bi bi-file-earmark-pdf"></i> Course Material (PDF, DOC, PPT)
                            </label>
                            <?php if (!empty($course['course_material'])): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-file-earmark-check"></i> Current file: 
                                    <strong><?php echo htmlspecialchars($course['course_material']); ?></strong>
                                </div>
                            <?php endif; ?>
                            <input 
                                type="file" 
                                class="form-control" 
                                id="course_material" 
                                name="course_material"
                                accept=".pdf,.doc,.docx,.ppt,.pptx"
                                onchange="previewFile(this)"
                            >
                            <div class="form-text">
                                Leave empty to keep current file. Upload new file to replace.
                            </div>
                            <div id="filePreview" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="video_link" class="form-label">
                                <i class="bi bi-play-circle"></i> Video Link
                            </label>
                            <input 
                                type="url" 
                                class="form-control" 
                                id="video_link" 
                                name="video_link" 
                                value="<?php echo htmlspecialchars($course['video_link']); ?>"
                                placeholder="https://www.youtube.com/watch?v=..."
                            >
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Update Course
                            </button>
                            <a href="courses.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Course Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Course Information
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Course ID:</strong> <?php echo $course['id']; ?></p>
                    <p class="mb-2"><strong>Created:</strong> <?php echo format_date($course['created_at']); ?></p>
                    <p class="mb-2"><strong>Last Updated:</strong> <?php echo format_date($course['updated_at']); ?></p>
                    <p class="mb-0">
                        <strong>Status:</strong> 
                        <?php if ($course['status'] == 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="mt-3">
        <a href="courses.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>