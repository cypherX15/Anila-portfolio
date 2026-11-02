# Anila S — Personal Engineering Portfolio

A modern, minimal, dark-themed personal portfolio and software showcase built with PHP, MySQL, HTML5, CSS3, and JavaScript, designed to run locally on Windows via XAMPP.

---

## 🚀 Quick Start Guide (How to Run Your Website)

### Step 1: Start Apache and MySQL in XAMPP
1. Open the **XAMPP Control Panel** on your Windows computer.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Both module indicators should turn green.

---

### Step 2: Initialize the Database (Two Easy Ways)

#### Method A: One-Click Browser Initializer (Recommended)
Once Apache and MySQL are running in XAMPP, open your browser and navigate to:
```
http://localhost/portfolio/database/seed.php
```
This script will automatically create the `anila_portfolio` database, build all required relational tables, and seed them with your authentic profile details, education, technical skills, projects, and sample comments.

#### Method B: Import via phpMyAdmin
1. Open your browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Click **New** in the left sidebar to create a database named `anila_portfolio` (or let the SQL file handle creation).
3. Click the **Import** tab at the top.
4. Click **Choose File** and select:
   `C:\xampp\htdocs\portfolio\database\schema.sql`
5. Click **Go** at the bottom to execute the import.

---

### Step 3: Access Your Website & Admin Portal

* **Public Portfolio Website:**  
  [http://localhost/portfolio/index.php](http://localhost/portfolio/index.php)

* **Administrator Login Portal:**  
  [http://localhost/portfolio/admin/login.php](http://localhost/portfolio/admin/login.php)

* **Administrator Credentials:**
  - **Username / Email:** `admin` or `anila994785@gmail.com`
  - **Password:** `admin123`

---

## 🛠️ Architecture & Features Overview

### 1. Visual Design & Presentation
- **Theme:** Minimalist dark aesthetic with subtle violet/indigo/cyan accents.
- **Hero Section:** High-impact minimal design with abstract code graphic, personal headline, and direct project navigation.
- **Editorial About:** Compact 2-paragraph background overview with concise academic education.
- **Skills Grid:** Organized by Programming Languages, Web Development, App Development, and Database & Tools — with **zero percentage bars**.
- **Project Showcase:** The center highlight of the homepage with large visual previews, category pills, taglines, and direct links to individual project pages.

### 2. Individual Project Details Pages (`project.php`)
- Dedicated layout for each project showcasing:
  - Full project overview and problem statement
  - Key bulleted objectives
  - Implementation workflows
  - Interactive screenshot lightbox gallery
  - Demonstration video embed
  - PDF technical documentation download
  - Direct links to GitHub repositories and live websites
  - **Project-Specific Comments Section:** Only comments linked to the given project are displayed and stored.

### 3. Project Access Request System (`request_access.php`)
- Protects confidential project resources (source code, demonstration videos, reports) behind an access request barrier.
- Visitors submit their Name, Email, and Reason for access.
- Submissions are marked as `pending` until approved in the admin dashboard.
- Direct URL protection checks access authorization.

### 4. Admin Dashboard (`/admin/`)
- **Dashboard Overview (`admin/index.php`):** Real-time statistics on total projects, access requests, comments, and unread messages.
- **Project Management (`admin/projects.php`):** Full CRUD controls with tabbed editor, image thumbnail upload, screenshot gallery management, PDF report uploads, and restricted toggles.
- **Access Request Management (`admin/access_requests.php`):** View, approve, reject, or delete visitor requests.
- **Project Comments Moderation (`admin/comments.php`):** Filter comments by project, review pending entries, and approve/reject before public display.
- **Contact Inquiries (`admin/messages.php`):** Read messages, toggle read/unread status, and reply directly via email.
- **Skills Management (`admin/skills.php`):** Add, update, and categorize technical skills.

---

## 🔒 Security Practices Implemented
- **PDO Prepared Statements:** Parameterized queries across all database transactions to prevent SQL injection.
- **Password Hashing:** Administrative passwords stored securely using `password_hash()` (Bcrypt).
- **CSRF Tokens:** All sensitive form submissions protected via session-based CSRF tokens.
- **Output Sanitization:** Strict `htmlspecialchars()` escaping on all rendered visitor inputs.
- **Secure File Storage:** File uploads validated by extension and size; `.htaccess` denies direct script execution inside the uploads folder.
- **Protected Admin Routes:** Enforced through `requireAdmin()` session authentication guards.
