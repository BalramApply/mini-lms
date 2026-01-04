<?php
// Helper Functions

// Sanitize input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is student
function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check authentication and redirect
function check_auth() {
    if (!is_logged_in()) {
        redirect(BASE_URL . 'login.php');
    }
}

// Check admin authentication
function check_admin() {
    check_auth();
    if (!is_admin()) {
        redirect(BASE_URL . 'index.php');
    }
}

// Check student authentication
function check_student() {
    check_auth();
    if (!is_student()) {
        redirect(BASE_URL . 'index.php');
    }
}

// Display success message
function show_success($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

// Display error message
function show_error($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

// Display warning message
function show_warning($message) {
    return '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

// Format date
function format_date($date) {
    return date('d M Y', strtotime($date));
}

// Format datetime
function format_datetime($datetime) {
    return date('d M Y h:i A', strtotime($datetime));
}

// Get user details by ID
function get_user_by_id($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, status FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Count total courses
function count_total_courses($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE status = 'active'");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Count total students
function count_total_students($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student' AND status = 'active'");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Count total enrollments
function count_total_enrollments($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Check if student is enrolled in course
function is_enrolled($conn, $student_id, $course_id) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $student_id, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

// Upload file function
function upload_file($file, $allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx']) {
    $target_dir = UPLOAD_DIR;
    
    // Create upload directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($file["name"]);
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if file extension is allowed
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed_extensions)];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File size must be less than 5MB'];
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
}
?>