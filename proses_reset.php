<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konfirmasi_reset'])) {
    try {
        // Kosongkan tabel transaksi dan reset auto_increment ID ke 1
        $pdo->exec("TRUNCATE TABLE transactions");

        // Opsional: Jika kamu ingin membersihkan kategori hasil ketik manual juga, aktifkan baris di bawah:
        // $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE categories; SET FOREIGN_KEY_CHECKS = 1;");

        header("Location: index.php?status=reset_success");
        exit();
    } catch (PDOException $e) {
        die("Gagal mengosongkan database: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}