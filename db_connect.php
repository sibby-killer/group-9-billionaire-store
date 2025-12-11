<?php
// Database Credentials
$host = 'your-rds-endpoint.amazonaws.com';
$db   = 'postgres';
$user = 'your_db_user';
$pass = 'your_db_password';
$port = '5432';

// S3 Credentials
$bucket_name = 'your-bucket-name';
$region      = 'us-east-1'; // e.g., us-east-1
$access_key  = 'YOUR_AWS_ACCESS_KEY';
$secret_key  = 'YOUR_AWS_SECRET_KEY';

// DSN for PDO
$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
