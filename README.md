# Job-Portal-Project
A Job Portal system developed as a BCA project to streamline the recruitment process by connecting job seekers and recruiters on a single platform.


# 🎓 Student-Job Portal

A responsive, role-based web application that connects students with companies for job applications. Features real-time application tracking, dynamic filtering, notification system, and a modern glassmorphism UI. Built strictly with core PHP, MySQL, and Bootstrap 5.

---

## 🛠️ Tech Stack
- **Backend:** PHP 8+ (Object-Oriented + PDO)
- **Database:** MySQL / phpMyAdmin
- **Frontend:** HTML5, CSS3, Bootstrap 5, Vanilla JavaScript
- **Server:** Apache (XAMPP Local / InfinityFree Live)
- **Security:** Prepared Statements, Password Hashing, Session Flags, `.htaccess` Rules, Error Handling

---

## ✨ Features Implemented

### 👨‍ Student Portal
- Secure Registration & Login (Role-based routing)
- Dashboard with Stats & Quick Actions
- Browse & Filter Jobs (Keyword, City, Field)
- View Job Details & Apply with One Click
- Track Applications (View Status, Withdraw if `applied`)
- Notification Inbox (Auto-alerts on Shortlist)
- Edit Profile & Manage Skills (Add/Remove dynamically)

### 🏢 Company Portal
- Secure Registration & Login
- Dashboard with Job & Application Analytics
- Post New Jobs & Manage Existing Ones (Toggle Active/Closed, Delete)
- Browse Students (Filter by Name, City, Field)
- View Full Student Profiles & Send Shortlist Notifications
- Review Applications & Update Status (`Applied → Shortlisted → Interview → Hired/Rejected`)
- Edit Company Profile

### 🌐 General & Architecture
- Modern Landing Page with Hero Section & Static Featured Jobs
- Responsive Glassmorphism Design System
- Clean URLs via `.htaccess` (`/login` instead of `/login.php`)
- Centralized Config (`includes/config.php`) for easy deployment
- Reusable Header/Footer Components (`includes/header.php`, `includes/footer.php`)
- Cross-Device Compatible UI
- Production-Ready Security & Error Handling

---

## 📂 Project Structure
portal_project/
├── includes/
│ ├── db.php # Secure PDO connection handler
│ ├── config.php # Database credentials (gitignored)
│ ├── header.php # Common navbar, meta, CSS
│ └── footer.php # Common footer, JS, closing tags
├── student/
│ ├── dashboard.php
│ ├── view_jobs.php
│ ├── job_details.php
│ ├── my_applications.php
│ ├── notifications.php
│ ├── profile.php
│ └── skills.php
├── company/
│ ├── dashboard.php
│ ├── post_job.php
│ ├── manage_jobs.php
│ ├── browse_students.php
│ ├── student_profile.php
│ ├── view_applications.php
│ └── profile.php
├── index.php # Public Landing Page
├── login.php
├── register.php
├── logout.php
├── .htaccess # Clean URLs, Directory Browsing Block, File Protection
└── README.md


---

## ⚙️ Detailed Setup & Usage Guide

### 📦 Prerequisites
- [XAMPP](https://www.apachefriends.org/) (v8.1+ recommended) with Apache & MySQL enabled
- Modern Web Browser (Chrome, Edge, Firefox)
- VS Code or any code editor (optional)

### 🚀 Step-by-Step Installation

#### 1️⃣ Project Placement
- Extract/clone the project folder.
- Move the entire folder to:  
  `C:\xampp\htdocs\portal_project\`  
  *(Path should look like: `htdocs/portal_project/index.php`)*

#### 2️⃣ Start Local Server
- Open **XAMPP Control Panel**.
- Click **Start** for `Apache` and `MySQL`.
- Ensure both indicators turn **Green**.

#### 3️⃣ Database Setup
1. Open browser → `http://localhost/phpmyadmin`
2. Click **"New"** (left sidebar)
3. Database name: `student_job_portal`
4. Collation: `utf8mb4_general_ci` → Click **Create**
5. Select the new database → Click **"Import"** tab
6. Click **"Choose File"** → Select the provided `student_job_portal.sql`
7. Click **"Go"** → Wait for success message ✅

#### 4️⃣ Database Configuration
- Navigate to: `portal_project/includes/config.php`
- Verify/Update credentials (default XAMPP setup):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_job_portal');
define('DB_USER', 'root');
define('DB_PASS', ''); // Leave blank for default XAMPP
