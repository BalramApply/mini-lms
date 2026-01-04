<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Register';

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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (!validate_email($email)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email already registered. Please use a different email or login.';
        } else {
            // Hash password
            $hashed_password = hash_password($password);
            
            // Insert new user
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
            
            if (mysqli_stmt_execute($stmt)) {
                // Registration successful, redirect to login
                redirect('login.php?registered=success');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow-lg">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus"></i> Create Your Account
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <?php echo show_error($error); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person"></i> Full Name
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="name" 
                                name="name" 
                                placeholder="Enter your full name"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                required
                                minlength="3"
                            >
                            <div class="invalid-feedback">
                                Please enter your full name (minimum 3 characters)
                            </div>
                        </div>
                        
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
                            <div class="invalid-feedback">
                                Please enter a valid email address
                            </div>
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
                                    placeholder="Create a password"
                                    required
                                    minlength="6"
                                >
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button"
                                    onclick="togglePassword('password', 'toggleIcon1')"
                                >
                                    <i class="bi bi-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Password must be at least 6 characters long
                            </div>
                            <div class="invalid-feedback">
                                Please enter a password (minimum 6 characters)
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill"></i> Confirm Password
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    placeholder="Confirm your password"
                                    required
                                    minlength="6"
                                >
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button"
                                    onclick="togglePassword('confirm_password', 'toggleIcon2')"
                                >
                                    <i class="bi bi-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Please confirm your password
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-person-plus"></i> Register
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-decoration-none fw-bold">
                                Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side password match validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>