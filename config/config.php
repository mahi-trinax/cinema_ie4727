<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// use docker env vars if present, otherwise fall back to local defaults
$host   = getenv('MYSQL_HOST')     ?: 'localhost';
$user   = getenv('MYSQL_USER')     ?: 'root';
$pass   = getenv('MYSQL_PASSWORD') ?: '';
$dbname = getenv('MYSQL_DATABASE') ?: 'movie_booking';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
