<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
check_admin();

$page_title = 'Add New Course';

$error = '';
$success = '';

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
        $course_material = null;
        
        // Handle file upload
        if (isset($_FILES['course_material']) && $_FILES['course_material']['error'] == 0) {
            $upload_result = upload_file($_FILES['course_material']);
            
            if ($upload_result['success']) {
                $course_material = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        // If no error, insert course
        if (empty($error)) {
            $created_by = $_SESSION['user_id'];
            $stmt = mysqli_prepare($conn, "INSERT INTO courses (title, description, duration, course_material, video_link, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $description, $duration, $course_material, $video_link, $status, $created_by);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Course added successfully!';
                // Clear form
                $_POST = array();
            } else {
                $error = 'Failed to add course. Please try again.';
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
            <i class="bi bi-plus-circle"></i> Add New Course
        </h2>
        <p class="text-muted mb-0">Create a new course for students</p>
    </div>
    
    <!-- Alerts -->
    <?php if ($success): ?>
        <?php echo show_success($success); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo show_error($error); ?>
    <?php endif; ?>
    
    <!-- Add Course Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text"></i> Course Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="addCourseForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="bi bi-card-heading"></i> Course Title <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                placeholder="e.g., Introduction to PHP Programming"
                                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
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
                                placeholder="Provide a detailed description of the course"
                                required
                            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
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
                                        placeholder="e.g., 4 weeks, 20 hours"
                                        value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>"
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
                                        <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="course_material" class="form-label">
                                <i class="bi bi-file-earmark-pdf"></i> Course Material (PDF, DOC, PPT)
                            </label>
                            <input 
                                type="file" 
                                class="form-control" 
                                id="course_material" 
                                name="course_material"
                                accept=".pdf,.doc,.docx,.ppt,.pptx"
                                onchange="previewFile(this)"
                            >
                            <div class="form-text">
                                Upload course materials (Max size: 5MB)
                            </div>
                            <div id="filePreview" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="video_link" class="form-label">
                                <i class="bi bi-play-circle"></i> Video Link (YouTube, Vimeo, etc.)
                            </label>
                            <input 
                                type="url" 
                                class="form-control" 
                                id="video_link" 
                                name="video_link" 
                                placeholder="https://www.youtube.com/watch?v=..."
                                value="<?php echo isset($_POST['video_link']) ? htmlspecialchars($_POST['video_link']) : ''; ?>"
                            >
                            <div class="form-text">
                                Optional: Add a video tutorial link
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Add Course
                            </button>
                            <a href="courses.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Side Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Course Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Use clear and descriptive titles
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Provide detailed course descriptions
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Upload relevant course materials
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Add video links when available
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Set appropriate course duration
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Supported Formats
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Documents:</strong></p>
                    <ul class="small">
                        <li>PDF (.pdf)</li>
                        <li>Word (.doc, .docx)</li>
                        <li>PowerPoint (.ppt, .pptx)</li>
                    </ul>
                    <p class="mb-2 mt-3"><strong>Videos:</strong></p>
                    <ul class="small">
                        <li>YouTube links</li>
                        <li>Vimeo links</li>
                        <li>Any public video URL</li>
                    </ul>
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