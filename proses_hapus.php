<?php
require_once 'config/database.php';

// Pastikan ada parameter ID yang dikirim lewat URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Gunakan Prepared Statement untuk mencegah SQL Injection
        $sql = "DELETE FROM transactions WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi penghapusan data
        $stmt->execute([':id' => $id]);

        // Setelah berhasil, lempar kembali ke halaman utama dengan status sukses
        header("Location: index.php?status=deleted");
        exit();

    } catch (PDOException $e) {
        die("Gagal menghapus data: " . $e->getMessage());
    }
} else {
    // Jika mencoba akses langsung tanpa ID, kembalikan ke index
    header("Location: index.php");
    exit();
}