<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is student
check_student();

$page_title = 'Available Courses';

$student_id = $_SESSION['user_id'];

// Get all active courses
$courses_query = "
    SELECT c.*,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_enrollments,
    EXISTS(SELECT 1 FROM enrollments WHERE course_id = c.id AND student_id = ?) as is_enrolled
    FROM courses c
    WHERE c.status = 'active'
    ORDER BY c.created_at DESC
";
$stmt = mysqli_prepare($conn, $courses_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$courses = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-0">
            <i class="bi bi-journal-text"></i> Available Courses
        </h2>
        <p class="text-muted mb-0">Explore and enroll in courses to start learning</p>
    </div>
    
    <!-- Search Box -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    type="text" 
                    class="form-control" 
                    id="searchInput" 
                    placeholder="Search courses by title or description..."
                    onkeyup="searchCourses()"
                >
            </div>
        </div>
    </div>
    
    <!-- Courses Grid -->
    <?php if (mysqli_num_rows($courses) > 0): ?>
        <div class="row g-4" id="coursesGrid">
            <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                <div class="col-md-6 col-lg-4 course-item">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <?php if ($course['is_enrolled']): ?>
                                <span class="badge bg-success course-badge">
                                    <i class="bi bi-check-circle"></i> Enrolled
                                </span>
                            <?php else: ?>
                                <span class="badge bg-primary course-badge">
                                    <i class="bi bi-star-fill"></i> Available
                                </span>
                            <?php endif; ?>
                            
                            <h5 class="card-title mt-3 course-title">
                                <?php echo htmlspecialchars($course['title']); ?>
                            </h5>
                            
                            <p class="card-text text-muted course-description">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <div class="mb-3">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bi bi-clock"></i> <?php echo htmlspecialchars($course['duration']); ?>
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-people"></i> <?php echo $course['total_enrollments']; ?> students
                                </span>
                            </div>
                            
                            <?php if (!empty($course['course_material']) || !empty($course['video_link'])): ?>
                                <div class="mb-3">
                                    <?php if (!empty($course['course_material'])): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-file-earmark-pdf text-danger"></i> Study Material
                                        </small>
                                    <?php endif; ?>
                                    <?php if (!empty($course['video_link'])): ?>
                                        <small class="text-muted ms-2">
                                            <i class="bi bi-play-circle text-primary"></i> Video Tutorial
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid">
                                <?php if ($course['is_enrolled']): ?>
                                    <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-book-half"></i> View Course
                                    </a>
                                <?php else: ?>
                                    <a href="enroll.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Enroll Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>No courses available</h4>
                    <p class="text-muted">Check back soon for new courses!</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- No Results Message -->
    <div id="noResults" class="card mt-4" style="display: none;">
        <div class="card-body">
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h5>No courses found</h5>
                <p class="text-muted">Try different search terms</p>
            </div>
        </div>
    </div>
</div>

<script>
// Search courses
function searchCourses() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const courseItems = document.querySelectorAll('.course-item');
    let visibleCount = 0;
    
    courseItems.forEach(function(item) {
        const title = item.querySelector('.course-title').textContent.toLowerCase();
        const description = item.querySelector('.course-description').textContent.toLowerCase();
        
        if (title.includes(input) || description.includes(input)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const noResults = document.getElementById('noResults');
    if (visibleCount === 0 && input !== '') {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>