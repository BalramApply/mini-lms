# 📚 Mini Learning Management System (LMS)

A comprehensive web-based Learning Management System built with Core PHP, MySQL, and Bootstrap 5.

## 🎯 Project Overview

Mini LMS is a role-based learning platform that enables administrators to manage courses and students while allowing students to browse, enroll, and complete courses with progress tracking.

### Key Features

✅ **User Management**
- Role-based authentication (Admin & Student)
- Secure registration and login
- Password hashing with bcrypt
- Session-based access control

✅ **Admin Panel**
- Dashboard with real-time statistics
- Complete course management (CRUD operations)
- Student management (activate/deactivate accounts)
- Enrollment tracking and analytics
- File upload for course materials

✅ **Student Panel**
- Personalized dashboard with progress tracking
- Browse and search available courses
- One-click enrollment system
- Access to learning materials (PDF, videos)
- Course completion tracking

✅ **Security Features**
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Secure password storage
- File upload validation
- Session management

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| **Backend** | Core PHP 7.4+ |
| **Database** | MySQL 5.7+ |
| **Frontend** | HTML5, CSS3, Bootstrap 5 |
| **JavaScript** | Vanilla JS (no jQuery) |
| **Server** | Apache (XAMPP/WAMP) |

---

## 📦 Installation Guide

### Prerequisites

- XAMPP/WAMP/LAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Download Project

```bash
# Clone or download the project
git clone https://github.com/yourusername/mini-lms.git

# Or extract ZIP file to:
C:/xampp/htdocs/mini-lms/  (Windows)
/var/www/html/mini-lms/    (Linux)
```

### Step 2: Database Setup

1. **Open phpMyAdmin**: `http://localhost/phpmyadmin`

2. **Create Database**:
   - Click "New" to create a database
   - Name: `mini_lms`
   - Collation: `utf8_general_ci`

3. **Import SQL Files**:
   - Select `mini_lms` database
   - Click "Import" tab
   - Choose `database/mini_lms.sql`
   - Click "Go"
   - Import `database/sample_data.sql` (optional, for test data)

### Step 3: Configuration

1. **Update Database Credentials**:

Open `config/database.php` and update:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
define('DB_NAME', 'mini_lms');
```

2. **Update Base URL** (if needed):

```php
define('BASE_URL', 'http://localhost/mini-lms/');
```

3. **Create Uploads Folder**:

```bash
# Create folder for course materials
mkdir uploads
chmod 777 uploads  # Linux/Mac only
```

### Step 4: Start Servers

1. Start Apache server
2. Start MySQL server
3. Navigate to: `http://localhost/mini-lms/`

---

## 🔐 Default Login Credentials

### Admin Account
```
Email:    admin@lms.com
Password: password123
```

### Student Accounts (Sample Data)
```
Email:    john@student.com
Password: password123

Email:    sarah@student.com
Password: password123

Email:    emily@student.com
Password: password123
```

---

## 📁 Project Structure

```
mini-lms/
│
├── config/
│   └── database.php              # Database connection & config
│
├── includes/
│   ├── header.php               # Common header
│   ├── footer.php               # Common footer
│   └── functions.php            # Helper functions
│
├── admin/
│   ├── index.php                # Admin dashboard
│   ├── courses.php              # Manage courses
│   ├── add_course.php           # Add new course
│   ├── edit_course.php          # Edit course
│   ├── delete_course.php        # Delete course
│   ├── students.php             # Manage students
│   ├── student_details.php      # Student profile
│   └── enrollments.php          # View enrollments
│
├── student/
│   ├── index.php                # Student dashboard
│   ├── courses.php              # Browse courses
│   ├── enroll.php               # Enrollment handler
│   ├── my_courses.php           # Enrolled courses
│   └── view_course.php          # View course materials
│
├── assets/
│   ├── css/
│   │   └── style.css            # Custom styles
│   └── js/
│       └── script.js            # Custom JavaScript
│
├── uploads/                      # Course materials storage
│
├── database/
│   ├── mini_lms.sql             # Database schema
│   └── sample_data.sql          # Sample data
│
├── index.php                     # Landing page
├── login.php                     # Login page
├── register.php                  # Registration page
├── logout.php                    # Logout handler
└── README.md                     # This file
```

---

## 📊 Database Schema

### users Table
```sql
- id (Primary Key)
- name
- email (Unique)
- password (Hashed)
- role (admin/student)
- status (active/inactive)
- created_at
- updated_at
```

### courses Table
```sql
- id (Primary Key)
- title
- description
- duration
- course_material (file path)
- video_link
- status (active/inactive)
- created_by (Foreign Key → users.id)
- created_at
- updated_at
```

### enrollments Table
```sql
- id (Primary Key)
- student_id (Foreign Key → users.id)
- course_id (Foreign Key → courses.id)
- enrollment_date
- completion_status (enrolled/in_progress/completed)
- completed_at
- UNIQUE (student_id, course_id)
```

---

## 🎯 User Flows

### Student Flow
1. Register account → Email verification
2. Login → Student dashboard
3. Browse courses → Search/Filter
4. Enroll in course → One-click enrollment
5. View course → Access materials
6. Start learning → Status: In Progress
7. Complete course → Status: Completed ✓

### Admin Flow
1. Login as admin
2. View dashboard → Statistics
3. Add new course → Upload materials
4. Manage students → Activate/Deactivate
5. Track enrollments → Analytics
6. Edit/Delete courses

---

## 🔧 Features in Detail

### 1. Authentication System
- Secure registration with validation
- Password hashing using bcrypt
- Session-based login
- Role-based access control
- Auto-redirect based on role

### 2. Course Management
- Create, Read, Update, Delete operations
- File upload (PDF, DOC, PPT) up to 5MB
- Video link integration (YouTube, Vimeo)
- Course status management
- Enrollment tracking

### 3. Student Management
- View all registered students
- Track enrollments and completions
- Activate/deactivate accounts
- Detailed student profiles
- Learning analytics

### 4. Learning System
- Browse active courses
- Real-time search functionality
- One-click enrollment
- Progress tracking (enrolled/in-progress/completed)
- Material download and video access

### 5. Dashboard Analytics
- Real-time statistics
- Completion rates
- Enrollment metrics
- Recent activity tracking

---

## 🔒 Security Measures

1. **Password Security**
   - Bcrypt hashing (PASSWORD_DEFAULT)
   - No plain text storage

2. **SQL Injection Prevention**
   - Prepared statements for all queries
   - Parameter binding with mysqli

3. **XSS Protection**
   - Input sanitization with htmlspecialchars()
   - Output encoding

4. **File Upload Security**
   - File type validation
   - Size limit enforcement (5MB)
   - Unique filename generation
   - Extension checking

5. **Session Security**
   - Secure session management
   - Session timeout on logout
   - Role-based access checks

---

## 🐛 Troubleshooting

### Database Connection Error
```
Solution: Check credentials in config/database.php
```

### File Upload Not Working
```
Solution: 
1. Create uploads/ folder
2. Set write permissions: chmod 777 uploads/
3. Check PHP upload_max_filesize setting
```

### Pages Not Loading (404)
```
Solution: Update BASE_URL in config/database.php
```

### Cannot Login as Admin
```
Solution: 
1. Check database has admin record
2. Default: admin@lms.com / password123
3. Re-import mini_lms.sql
```

### Styles Not Loading
```
Solution: Clear browser cache or check Bootstrap CDN links
```

---

## 🚀 Deployment Checklist

- [ ] Update database credentials for production
- [ ] Change default admin password
- [ ] Set secure file permissions
- [ ] Enable HTTPS/SSL
- [ ] Configure error reporting (off in production)
- [ ] Set up regular database backups
- [ ] Update BASE_URL to production domain
- [ ] Test all features thoroughly

---

## 📈 Future Enhancements

- [ ] Certificate generation on course completion
- [ ] Quiz and assessment module
- [ ] Email notifications (enrollment, completion)
- [ ] Advanced analytics dashboard
- [ ] Course categories and tags
- [ ] Student discussion forum
- [ ] Payment gateway integration
- [ ] Mobile app development
- [ ] API for third-party integration
- [ ] Multi-language support

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

---

## 👨‍💻 Developer

**Your Name**
- Email: your.email@example.com
- LinkedIn: [Your LinkedIn Profile]
- GitHub: [Your GitHub Profile]

---

## 📄 License

This project is created for educational purposes.

---

## 📞 Support

For support, email your.email@example.com or create an issue in the repository.

---

## ⭐ Show Your Support

Give a ⭐️ if this project helped you!

---

**Built with ❤️ for NIIT Interview**
