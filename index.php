<?php
require_once 'db_connection.php';

$student = null;
$error_message = '';
$search_roll = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['roll_number'])) {
    $search_roll = trim($_GET['roll_number']);

    if ($search_roll === '') {
        $error_message = 'Please enter a valid Roll Number to search.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM students WHERE roll_number = :roll_number LIMIT 1');
            $stmt->execute([':roll_number' => $search_roll]);
            $student = $stmt->fetch();

            if (!$student) {
                $error_message = "No academic record found for Roll Number \"" . htmlspecialchars($search_roll, ENT_QUOTES, 'UTF-8') . "\". Please verify the roll number and try again.";
            }
        } catch (PDOException $e) {
            $error_message = 'An unexpected error occurred while retrieving the result. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Portal | Academic Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation Header -->
    <header class="navbar">
        <div class="container navbar-content">
            <a href="index.php" class="brand">
                <svg class="icon icon-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                <span>EduResult Portal</span>
            </a>

            <nav class="nav-actions">
                <button type="button" id="theme-toggle" class="btn-icon" aria-label="Toggle Dark or Light Theme" title="Toggle Theme">
                    <!-- Sun Icon (shown in dark mode) -->
                    <svg class="icon icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <!-- Moon Icon (shown in light mode) -->
                    <svg class="icon icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <a href="admin_login.php" class="btn btn-outline">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Admin Login</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="container main-content">
        <!-- Search Section -->
        <section class="card search-card">
            <div class="card-header">
                <h1>Online Student Result Lookup</h1>
                <p>Enter your unique institutional Roll Number below to view and print your statement of marks.</p>
            </div>

            <form id="search-form" action="index.php" method="GET" novalidate>
                <div class="search-bar-group">
                    <div class="input-icon-wrapper">
                        <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            type="text"
                            id="roll_number"
                            name="roll_number"
                            class="form-control"
                            placeholder="e.g., STU-2026-101"
                            value="<?php echo htmlspecialchars($search_roll, ENT_QUOTES, 'UTF-8'); ?>"
                            autocomplete="off"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Search Result</span>
                    </button>
                </div>
                <p id="search-error" class="validation-error" role="alert" hidden></p>
            </form>
        </section>

        <!-- Server-Side Error Alert -->
        <?php if ($error_message !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <svg class="icon alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <strong>Result Not Found</strong>
                    <p><?php echo $error_message; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dynamic Report Card -->
        <?php if ($student): ?>
            <section class="card report-card" id="printable-report">
                <div class="report-header">
                    <div class="report-title-group">
                        <span class="badge badge-term"><?php echo htmlspecialchars($student['exam_term'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h2>Official Statement of Marks</h2>
                    </div>
                    <div class="report-actions no-print">
                        <button type="button" id="print-btn" class="btn btn-outline">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            <span>Print Report</span>
                        </button>
                    </div>
                </div>

                <!-- Student Profile Metadata -->
                <div class="student-meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">
                            <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Student Name
                        </span>
                        <strong class="meta-value"><?php echo htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Roll Number</span>
                        <strong class="meta-value"><?php echo htmlspecialchars($student['roll_number'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Class / Section</span>
                        <strong class="meta-value"><?php echo htmlspecialchars($student['class'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Result Status</span>
                        <?php $is_pass = ($student['grade'] !== 'F'); ?>
                        <strong class="meta-value">
                            <span class="status-pill <?php echo $is_pass ? 'status-pass' : 'status-fail'; ?>">
                                <?php echo $is_pass ? 'PASSED' : 'FAILED'; ?>
                            </span>
                        </strong>
                    </div>
                </div>

                <!-- Subject-Wise Marks Breakdown -->
                <div class="table-responsive">
                    <table class="marks-table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th class="text-center">Maximum Marks</th>
                                <th class="text-center">Passing Marks</th>
                                <th class="text-center">Marks Obtained</th>
                                <th class="text-center">Subject Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subjects = [
                                ['code' => 'MAT-101', 'name' => 'Mathematics', 'marks' => (int)$student['maths']],
                                ['code' => 'SCI-102', 'name' => 'Science', 'marks' => (int)$student['science']],
                                ['code' => 'ENG-103', 'name' => 'English Literature & Language', 'marks' => (int)$student['english']],
                                ['code' => 'CSC-104', 'name' => 'Computer Science', 'marks' => (int)$student['computer']],
                            ];
                            foreach ($subjects as $sub):
                                $sub_pass = $sub['marks'] >= 35;
                            ?>
                                <tr>
                                    <td class="font-mono"><?php echo $sub['code']; ?></td>
                                    <td><strong><?php echo $sub['name']; ?></strong></td>
                                    <td class="text-center">100</td>
                                    <td class="text-center">35</td>
                                    <td class="text-center font-bold"><?php echo $sub['marks']; ?></td>
                                    <td class="text-center">
                                        <span class="status-pill <?php echo $sub_pass ? 'status-pass' : 'status-fail'; ?>">
                                            <?php echo $sub_pass ? 'Pass' : 'Fail'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Metrics -->
                <div class="summary-grid">
                    <div class="summary-box">
                        <span class="summary-label">Total Marks</span>
                        <span class="summary-value"><?php echo (int)$student['total_marks']; ?> <small>/ 400</small></span>
                    </div>
                    <div class="summary-box">
                        <span class="summary-label">Percentage</span>
                        <span class="summary-value"><?php echo number_format((float)$student['percentage'], 2); ?>%</span>
                    </div>
                    <div class="summary-box">
                        <span class="summary-label">Final Grade</span>
                        <span class="summary-value grade-badge grade-<?php echo strtolower(htmlspecialchars($student['grade'], ENT_QUOTES, 'UTF-8')); ?>">
                            Grade <?php echo htmlspecialchars($student['grade'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer class="footer no-print">
        <div class="container">
            <p>Student Result Management System. Built with HTML5, CSS3, ES6 JavaScript, PHP, and MySQL.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>