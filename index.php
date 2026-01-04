<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Home';

// If user is already logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    if (is_admin()) {
        redirect(ADMIN_URL);
    } else {
        redirect(STUDENT_URL);
    }
}

// Get featured courses
$featured_courses = mysqli_query($conn, "SELECT * FROM courses WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-4 fw-bold mb-4">
                    <i class="bi bi-mortarboard-fill"></i> Welcome to Mini LMS
                </h1>
                <p class="lead mb-4">
                    Transform your learning journey with our comprehensive Learning Management System. 
                    Access quality courses, track your progress, and achieve your educational goals.
                </p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="register.php" class="btn btn-light btn-lg px-4 me-md-2">
                        <i class="bi bi-person-plus"></i> Get Started
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose Mini LMS?</h2>
            <p class="text-muted">Everything you need for effective online learning</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="bi bi-book-fill text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Quality Courses</h5>
                        <p class="card-text">
                            Access a wide range of courses designed by experts to help you master new skills.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="bi bi-graph-up-arrow text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Track Progress</h5>
                        <p class="card-text">
                            Monitor your learning progress and see how far you've come with detailed analytics.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="bi bi-award-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Get Certified</h5>
                        <p class="card-text">
                            Complete courses and earn certificates to showcase your achievements.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Courses</h2>
            <p class="text-muted">Explore our most popular courses</p>
        </div>
        
        <?php if (mysqli_num_rows($featured_courses) > 0): ?>
            <div class="row g-4">
                <?php while ($course = mysqli_fetch_assoc($featured_courses)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card course-card">
                            <div class="card-body">
                                <span class="badge bg-primary course-badge">
                                    <i class="bi bi-star-fill"></i> Featured
                                </span>
                                <h5 class="card-title mt-3"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo htmlspecialchars($course['duration']); ?>
                                    </span>
                                    <a href="login.php" class="btn btn-primary btn-sm">
                                        Enroll Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>No courses available yet</h4>
                <p>Check back soon for new courses!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="fw-bold mb-4">Ready to Start Learning?</h2>
                <p class="lead mb-4">
                    Join thousands of students already learning on Mini LMS. Sign up today and get access to all courses.
                </p>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-person-plus"></i> Create Free Account
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>