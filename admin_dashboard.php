<?php
session_start();
require_once 'db_connection.php';

// Protect route: Restrict access to authenticated administrators
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$flash_success = '';
$flash_error = '';

/**
 * Helper function to calculate total marks, percentage, and letter grade (A, B, C, D, F)
 */
function calculateResultMetrics(int $maths, int $science, int $english, int $computer): array {
    $total_marks = $maths + $science + $english + $computer;
    $percentage = round(($total_marks / 400) * 100, 2);

    // If a student scores below 35 in any individual subject or below 40% overall, assign Grade F
    if ($maths < 35 || $science < 35 || $english < 35 || $computer < 35 || $percentage < 40) {
        $grade = 'F';
    } elseif ($percentage >= 85) {
        $grade = 'A';
    } elseif ($percentage >= 70) {
        $grade = 'B';
    } elseif ($percentage >= 55) {
        $grade = 'C';
    } else {
        $grade = 'D';
    }

    return [
        'total_marks' => $total_marks,
        'percentage'  => $percentage,
        'grade'       => $grade
    ];
}

// Handle POST requests (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Delete Student Record
    if ($action === 'delete') {
        $delete_id = (int)($_POST['student_id'] ?? 0);
        if ($delete_id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
                $stmt->execute([':id' => $delete_id]);
                $flash_success = 'Student record deleted successfully.';
            } catch (PDOException $e) {
                $flash_error = 'Failed to delete student record.';
            }
        }
    }

    // 2. Save (Insert or Update) Student Record
    if ($action === 'save') {
        $student_id   = (int)($_POST['student_id'] ?? 0);
        $roll_number  = trim($_POST['roll_number'] ?? '');
        $student_name = trim($_POST['student_name'] ?? '');
        $class        = trim($_POST['class'] ?? '');
        $exam_term    = trim($_POST['exam_term'] ?? '');
        $maths        = filter_var($_POST['maths'] ?? null, FILTER_VALIDATE_INT);
        $science      = filter_var($_POST['science'] ?? null, FILTER_VALIDATE_INT);
        $english      = filter_var($_POST['english'] ?? null, FILTER_VALIDATE_INT);
        $computer     = filter_var($_POST['computer'] ?? null, FILTER_VALIDATE_INT);

        if (
            $roll_number === '' || $student_name === '' || $class === '' || $exam_term === '' ||
            $maths === false || $science === false || $english === false || $computer === false ||
            $maths < 0 || $maths > 100 || $science < 0 || $science > 100 ||
            $english < 0 || $english > 100 || $computer < 0 || $computer > 100
        ) {
            $flash_error = 'All fields are required and subject marks must be integers between 0 and 100.';
        } else {
            $metrics = calculateResultMetrics($maths, $science, $english, $computer);

            try {
                if ($student_id > 0) {
                    // Update existing record
                    $sql = 'UPDATE students
                            SET roll_number = :roll_number,
                                student_name = :student_name,
                                class = :class,
                                exam_term = :exam_term,
                                maths = :maths,
                                science = :science,
                                english = :english,
                                computer = :computer,
                                total_marks = :total_marks,
                                percentage = :percentage,
                                grade = :grade
                            WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':roll_number'  => $roll_number,
                        ':student_name' => $student_name,
                        ':class'        => $class,
                        ':exam_term'    => $exam_term,
                        ':maths'        => $maths,
                        ':science'      => $science,
                        ':english'      => $english,
                        ':computer'     => $computer,
                        ':total_marks'  => $metrics['total_marks'],
                        ':percentage'   => $metrics['percentage'],
                        ':grade'        => $metrics['grade'],
                        ':id'           => $student_id
                    ]);
                    $flash_success = "Updated result for Roll Number \"" . htmlspecialchars($roll_number, ENT_QUOTES, 'UTF-8') . "\" successfully.";
                } else {
                    // Insert new record
                    $sql = 'INSERT INTO students
                            (roll_number, student_name, class, exam_term, maths, science, english, computer, total_marks, percentage, grade)
                            VALUES
                            (:roll_number, :student_name, :class, :exam_term, :maths, :science, :english, :computer, :total_marks, :percentage, :grade)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':roll_number'  => $roll_number,
                        ':student_name' => $student_name,
                        ':class'        => $class,
                        ':exam_term'    => $exam_term,
                        ':maths'        => $maths,
                        ':science'      => $science,
                        ':english'      => $english,
                        ':computer'     => $computer,
                        ':total_marks'  => $metrics['total_marks'],
                        ':percentage'   => $metrics['percentage'],
                        ':grade'        => $metrics['grade']
                    ]);
                    $flash_success = "Added result for Roll Number \"" . htmlspecialchars($roll_number, ENT_QUOTES, 'UTF-8') . "\" successfully.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $flash_error = "Roll Number \"" . htmlspecialchars($roll_number, ENT_QUOTES, 'UTF-8') . "\" already exists. Please use a unique Roll Number or edit the existing record.";
                } else {
                    $flash_error = 'Database error occurred while saving the record.';
                }
            }
        }
    }
}

// Check if editing a record via GET parameter (?edit=ID)
$edit_record = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($edit_id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $edit_id]);
        $edit_record = $stmt->fetch();
    }
}

// Fetch all student records for the management table
$stmt = $pdo->query('SELECT * FROM students ORDER BY id DESC');
$students_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EduResult Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="navbar">
        <div class="container navbar-content">
            <div class="navbar-top-row">
                <a href="admin_dashboard.php" class="brand">
                    <svg class="icon icon-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    <span>Admin Control Panel</span>
                </a>
            </div>

            <nav class="nav-actions">
                <span class="admin-user-badge">
                    <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span><?php echo htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8'); ?></span>
                </span>

                <button type="button" id="theme-toggle" class="btn-icon" aria-label="Toggle Dark or Light Theme" title="Toggle Theme">
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
                    <svg class="icon icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <a href="index.php" class="btn btn-outline nav-btn">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span>Public Portal</span>
                </a>

                <a href="logout.php" class="btn btn-danger-outline nav-btn">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        <!-- Status Alerts -->
        <?php if ($flash_success !== ''): ?>
            <div class="alert alert-success" role="status">
                <svg class="icon alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div>
                    <strong>Success</strong>
                    <p><?php echo $flash_success; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($flash_error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <svg class="icon alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <strong>Operation Failed</strong>
                    <p><?php echo $flash_error; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Left Column: Mark Entry & Update Form -->
            <section class="card form-card" id="mark-entry-panel">
                <div class="card-header">
                    <h2 id="form-title"><?php echo $edit_record ? 'Update Student Result' : 'Register Student Marks'; ?></h2>
                    <p>Total marks, percentage, and final grade are computed automatically upon submission.</p>
                </div>

                <form id="mark-entry-form" action="admin_dashboard.php" method="POST" novalidate>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" id="student_id" name="student_id" value="<?php echo $edit_record ? (int)$edit_record['id'] : 0; ?>">

                    <div class="form-group">
                        <label for="roll_number">Roll Number</label>
                        <input
                            type="text"
                            id="roll_number"
                            name="roll_number"
                            class="form-control"
                            placeholder="e.g., STU-2026-104"
                            value="<?php echo $edit_record ? htmlspecialchars($edit_record['roll_number'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="student_name">Student Full Name</label>
                        <input
                            type="text"
                            id="student_name"
                            name="student_name"
                            class="form-control"
                            placeholder="e.g., Ananya Chatterjee"
                            value="<?php echo $edit_record ? htmlspecialchars($edit_record['student_name'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="class">Class / Section</label>
                            <input
                                type="text"
                                id="class"
                                name="class"
                                class="form-control"
                                placeholder="e.g., Grade 10 - Section A"
                                value="<?php echo $edit_record ? htmlspecialchars($edit_record['class'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="exam_term">Examination Term</label>
                            <select id="exam_term" name="exam_term" class="form-control" required>
                                <?php
                                $terms = ['Mid Term 2026', 'Final Term 2026', 'Supplementary 2026'];
                                $selected_term = $edit_record['exam_term'] ?? 'Final Term 2026';
                                foreach ($terms as $term):
                                ?>
                                    <option value="<?php echo htmlspecialchars($term, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($selected_term === $term) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($term, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="divider">
                    <h3 class="section-subtitle">Subject Marks (Out of 100)</h3>

                    <div class="form-row-2 subject-inputs-grid">
                        <div class="form-group">
                            <label for="maths">Mathematics</label>
                            <input
                                type="number"
                                id="maths"
                                name="maths"
                                class="form-control"
                                min="0"
                                max="100"
                                inputmode="numeric"
                                placeholder="0 - 100"
                                value="<?php echo $edit_record ? (int)$edit_record['maths'] : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="science">Science</label>
                            <input
                                type="number"
                                id="science"
                                name="science"
                                class="form-control"
                                min="0"
                                max="100"
                                inputmode="numeric"
                                placeholder="0 - 100"
                                value="<?php echo $edit_record ? (int)$edit_record['science'] : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="english">English</label>
                            <input
                                type="number"
                                id="english"
                                name="english"
                                class="form-control"
                                min="0"
                                max="100"
                                inputmode="numeric"
                                placeholder="0 - 100"
                                value="<?php echo $edit_record ? (int)$edit_record['english'] : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="computer">Computer Science</label>
                            <input
                                type="number"
                                id="computer"
                                name="computer"
                                class="form-control"
                                min="0"
                                max="100"
                                inputmode="numeric"
                                placeholder="0 - 100"
                                value="<?php echo $edit_record ? (int)$edit_record['computer'] : ''; ?>"
                                required
                            >
                        </div>
                    </div>

                    <p id="mark-form-error" class="validation-error" role="alert" hidden></p>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span id="submit-btn-text"><?php echo $edit_record ? 'Update Record' : 'Save Result'; ?></span>
                        </button>

                        <a href="admin_dashboard.php" id="cancel-edit-btn" class="btn btn-outline <?php echo $edit_record ? '' : 'hidden'; ?>">
                            <span>Cancel Edit</span>
                        </a>
                    </div>
                </form>
            </section>

            <!-- Right Column: Registered Students Data Table -->
            <section class="card table-card">
                <div class="card-header card-header-flex">
                    <div>
                        <h2>Registered Student Records</h2>
                        <p>Manage, update, or remove published student results.</p>
                    </div>
                    <span class="badge"><?php echo count($students_list); ?> Records</span>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Roll No.</th>
                                <th>Student Details</th>
                                <th class="text-center">Maths</th>
                                <th class="text-center">Sci</th>
                                <th class="text-center">Eng</th>
                                <th class="text-center">Comp</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Grade</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students_list)): ?>
                                <tr class="empty-row">
                                    <td colspan="10" class="text-center empty-state">
                                        No student records found. Use the form to add a new student result.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students_list as $row): ?>
                                    <tr>
                                        <td data-label="Roll No." class="font-mono font-bold">
                                            <?php echo htmlspecialchars($row['roll_number'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td data-label="Student Details">
                                            <div class="student-cell-wrapper">
                                                <div class="student-cell-name"><?php echo htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="student-cell-sub"><?php echo htmlspecialchars($row['class'], ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row['exam_term'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                        </td>
                                        <td data-label="Mathematics" class="text-center"><?php echo (int)$row['maths']; ?></td>
                                        <td data-label="Science" class="text-center"><?php echo (int)$row['science']; ?></td>
                                        <td data-label="English" class="text-center"><?php echo (int)$row['english']; ?></td>
                                        <td data-label="Computer" class="text-center"><?php echo (int)$row['computer']; ?></td>
                                        <td data-label="Total Marks" class="text-center font-bold"><?php echo (int)$row['total_marks']; ?>/400</td>
                                        <td data-label="Percentage" class="text-center"><?php echo number_format((float)$row['percentage'], 2); ?>%</td>
                                        <td data-label="Final Grade" class="text-center">
                                            <span class="grade-pill grade-<?php echo strtolower(htmlspecialchars($row['grade'], ENT_QUOTES, 'UTF-8')); ?>">
                                                <?php echo htmlspecialchars($row['grade'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td data-label="Actions" class="text-right actions-cell">
                                            <div class="action-buttons-group">
                                                <a
                                                    href="admin_dashboard.php?edit=<?php echo (int)$row['id']; ?>"
                                                    class="btn-action btn-edit"
                                                    title="Edit Record"
                                                    data-student='<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>'
                                                >
                                                    <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                    <span>Edit</span>
                                                </a>

                                                <form action="admin_dashboard.php" method="POST" class="inline-delete-form">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="student_id" value="<?php echo (int)$row['id']; ?>">
                                                    <button type="submit" class="btn-action btn-delete" title="Delete Record">
                                                        <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>