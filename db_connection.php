<?php
/**
 * db_connection.php
 * Handles secure PDO database connection with error handling.
 */

$db_host = '127.0.0.1';
$db_name = 'school_results';
$db_user = 'root';
$db_pass = ''; // Default XAMPP MySQL password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('Database Connection Error: Unable to connect to the database. Please verify that MySQL is running in XAMPP and the school_results database has been imported.');
}
?>