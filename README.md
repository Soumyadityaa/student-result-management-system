# Student Result Management System (EduResult Portal)

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?style=flat-square&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Semantic-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Grid_%26_Flexbox-1572B6?style=flat-square&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

A complete, production-ready, full-stack **Student Result Management System** designed for educational institutions. Built using native PHP (PDO), MySQL, semantic HTML5, modern CSS3, and vanilla ES6+ JavaScript, this web application provides a public portal for students to look up and print their academic report cards, alongside a session-protected administrative dashboard for teachers to manage records.

---

## Key Features

### Public Student Portal (`index.php`)
- **Dynamic Roll Number Search:** Instant lookup of student academic records using a unique institutional Roll Number.
- **Client & Server Validation:** Prevents blank submissions on the client side and gracefully handles non-existent roll numbers on the server side.
- **Comprehensive Statement of Marks:** Displays student metadata, subject-wise marks breakdown, individual pass/fail indicators, aggregate marks, percentage, and final letter grade.
- **Print-Ready Report Cards:** Dedicated `@media print` styles and one-click print action that hides navigation and search bars for clean physical or PDF transcripts.

### Administrator Control Panel (`admin_login.php` & `admin_dashboard.php`)
- **Secure Authentication:** Admin login verified against bcrypt password hashes (`password_hash` / `password_verify`) and protected via PHP sessions with session ID regeneration.
- **Automated Result Computation:** Automatically calculates `total_marks` (out of 400), `percentage`, and assigns a letter `grade` (`A`, `B`, `C`, `D`, `F`) on the backend before saving to MySQL.
- **Full CRUD Functionality:** Add new student marks, edit existing records with instant client-side form population, and delete records with confirmation prompts.

### UI/UX & Responsive Design
- **Persistent Dark / Light Theme:** Built with CSS Custom Properties (`:root` and `[data-theme="dark"]`) and synced with `localStorage` and OS color scheme preferences.
- **Mobile-First Responsiveness:** Uses CSS Grid and Flexbox. On mobile viewports (`<= 768px`), the 10-column admin data table automatically transforms into touch-friendly stacked data cards.
- **Zero-Dependency Inline SVGs:** Uses clean, scalable inline SVG icons throughout the interface with zero external icon library dependencies.

---

## Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Frontend Markup** | Semantic HTML5 |
| **Styling & Layout** | CSS3 (Custom Properties, Grid, Flexbox, Print Media Queries) |
| **Client-Side Scripting** | Vanilla JavaScript (ES6+) |
| **Backend Runtime** | PHP 8.x (PDO with Prepared Statements) |
| **Database** | MySQL / MariaDB (`utf8mb4` character set) |
| **Local Server Environment** | XAMPP / WAMP / MAMP / LAMP |

---

## Project Directory Structure

```text
student-result-system/
│
├── database.sql          # MySQL database schema and seed data (admin + sample students)
├── db_connection.php     # Centralized PDO database connection configuration
├── index.php             # Public Student Result Search Portal & Printable Report Card
├── admin_login.php       # Administrator authentication page
├── admin_dashboard.php   # Protected Mark Entry & Student Records CRUD Panel
├── logout.php            # Session termination and cookie cleanup script
├── style.css             # Responsive stylesheet with Light/Dark theme variables
├── script.js             # Theme persistence, form validation, and instant edit logic
└── README.md             # Project documentation
```

---

## Database Schema

The application uses a MySQL database named `school_results` containing two tables:

### 1. `admin` Table
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY, AUTO_INCREMENT` | Unique administrator ID |
| `username` | `VARCHAR(50)` | `NOT NULL, UNIQUE` | Administrator login username |
| `password` | `VARCHAR(255)` | `NOT NULL` | Bcrypt hashed password |

### 2. `students` Table
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY, AUTO_INCREMENT` | Unique student record ID |
| `roll_number` | `VARCHAR(50)` | `NOT NULL, UNIQUE` | Institutional Roll Number |
| `student_name` | `VARCHAR(100)` | `NOT NULL` | Student full name |
| `class` | `VARCHAR(50)` | `NOT NULL` | Class and section |
| `maths` | `INT` | `NOT NULL, CHECK (0-100)` | Mathematics marks |
| `science` | `INT` | `NOT NULL, CHECK (0-100)` | Science marks |
| `english` | `INT` | `NOT NULL, CHECK (0-100)` | English marks |
| `computer` | `INT` | `NOT NULL, CHECK (0-100)` | Computer Science marks |
| `total_marks` | `INT` | `NOT NULL` | Computed sum of 4 subjects (max 400) |
| `percentage` | `DECIMAL(5,2)` | `NOT NULL` | Computed overall percentage |
| `grade` | `VARCHAR(5)` | `NOT NULL` | Assigned letter grade (`A` - `F`) |
| `exam_term` | `VARCHAR(50)` | `NOT NULL` | Examination term name |

---

## Grading Scale & Calculation Rules

Marks are entered out of **100** for each of the four subjects (**Maximum Total = 400**). The backend automatically applies the following grading rules:

| Condition | Assigned Grade | Status |
| :--- | :---: | :---: |
| Percentage `>= 85%` (and `>= 35` in all subjects) | **A** | Passed |
| Percentage `>= 70%` and `< 85%` (and `>= 35` in all subjects) | **B** | Passed |
| Percentage `>= 55%` and `< 70%` (and `>= 35` in all subjects) | **C** | Passed |
| Percentage `>= 40%` and `< 55%` (and `>= 35` in all subjects) | **D** | Passed |
| Percentage `< 40%` **OR** `< 35` marks in any individual subject | **F** | Failed |

---

## Installation & Setup (XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any PHP 8.x + MySQL environment) installed on your machine.

### Step 1: Clone or Download the Repository
Clone this repository directly into your XAMPP `htdocs` directory:

```bash
cd C:/xampp/htdocs
git clone [https://github.com/your-username/student-result-management-system.git](https://github.com/your-username/student-result-management-system.git) student-result-system
```
*(Alternatively, download the ZIP file and extract it to `C:\xampp\htdocs\student-result-system\`)*

### Step 2: Start Apache and MySQL
Open the **XAMPP Control Panel** and start both the **Apache** and **MySQL** services.

### Step 3: Import the Database
1. Open your browser and go to `http://localhost/phpmyadmin/`.
2. Click on the **SQL** tab at the top.
3. Open `database.sql` from the project folder, copy its entire contents, paste it into the SQL query box, and click **Go**.

### Step 4: Configure Database Credentials (If Necessary)
By default, `db_connection.php` is configured for standard XAMPP settings:
```php
$db_host = '127.0.0.1';
$db_name = 'school_results';
$db_user = 'root';
$db_pass = '';
```
If your local MySQL root account uses a password, update `$db_pass` in `db_connection.php`.

### Step 5: Launch the Application
- **Public Student Portal:** `http://localhost/student-result-system/index.php`
- **Admin Login Page:** `http://localhost/student-result-system/admin_login.php`

---

## Default Credentials & Sample Data

### Administrator Login
- **Username:** `admin`
- **Password:** `password`

### Sample Student Roll Numbers (Pre-loaded via `database.sql`)
- `STU-2026-101` (Aarav Sharma — Grade A)
- `STU-2026-102` (Meera Nair — Grade B)
- `STU-2026-103` (Rohan Verma — Grade C)

---

## Security Practices Implemented

- **SQL Injection Prevention:** All database queries use PDO Prepared Statements with emulated prepares disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
- **Cross-Site Scripting (XSS) Mitigation:** All dynamic user and database outputs are escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Password Security:** Admin passwords are stored using PHP `password_hash()` (bcrypt) and verified via `password_verify()`.
- **Session Hardening:** Uses `session_regenerate_id(true)` upon login to prevent session fixation and properly invalidates session cookies upon logout.

---

## License

This project is open-source and available under the [MIT License](LICENSE).
