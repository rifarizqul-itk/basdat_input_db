<?php
$host = "localhost";
$user = "root";
$pass = ""; // Default Laragon kosong
$db   = "db_lifting";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Set error mode ke exception agar mudah debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}
?>