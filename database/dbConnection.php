<?php
/* <------------ dbConnection.php ------------>
 *  Database Connection (Shared by all pages)
 */

// <----- Database credentials (Change if needed) ----->
$host = '127.0.0.1';
$dbname = 'smart_retail_db';
$username = 'root';
$password = 'Masilo#25'; // Change this to your actual password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
?>
