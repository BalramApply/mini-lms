<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Login';

// If user is already logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    if (is_admin()) {
        redirect(ADMIN_URL);
    } else {
        redirect(STUDENT_URL);
    }
}

$error = '';
$success = '';

// Check for registration success message
if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success = 'Registration successful! Please login with your credentials.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!validate_email($email)) {
        $error = 'Invalid email format';
    } else {
        // Check user credentials
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role, status FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Check if account is active
            if ($user['status'] == 'inactive') {
                $error = 'Your account has been deactivated. Please contact administrator.';
            } 
            // Verify password
            elseif (verify_password($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    redirect(ADMIN_URL);
                } else {
                    redirect(STUDENT_URL);
                }
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-box-arrow-in-right"></i> Login to Mini LMS
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <?php echo show_error($error); ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <?php echo show_success($success); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="Enter your email"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                required
                            >
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password"
                                    required
                                >
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button"
                                    onclick="togglePassword('password', 'toggleIcon')"
                                >
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="register.php" class="text-decoration-none fw-bold">
                                Register here
                            </a>
                        </p>
                    </div>
                    
                    <!-- Demo Credentials -->
                    <div class="alert alert-info mt-3 small">
                        <strong><i class="bi bi-info-circle"></i> Demo Credentials:</strong><br>
                        <strong>Admin:</strong> admin@lms.com / 123456<br>
                        <strong>Student:</strong> Register a new account
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>