<?php
require_once 'config/database.php';

// Set header agar browser mendownload file sebagai CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Rekap_Keuangan_' . date('Y-m-d') . '.csv');

// Buka output stream untuk menulis data CSV
$output = fopen('php://output', 'w');

// 1. Tulis Baris Judul Kolom (Header) yang Informatif
fputcsv($output, ['Tanggal', 'Kategori', 'Jenis', 'Keterangan', 'Nominal (Rp)']);

try {
    // 2. Query ambil semua data transaksi digabung dengan nama dan tipe kategori
    $sql = "SELECT t.date, c.name AS category_name, c.type AS category_type, t.description, t.amount 
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            ORDER BY t.date DESC, t.id DESC";
            
    $stmt = $pdo->query($sql);

    // 3. Looping data dan masukkan ke dalam file CSV
    while ($row = $stmt->fetch(PDO::class === 'PDO' ? PDO::FETCH_ASSOC : 2)) {
        // Terjemahkan jenis agar lebih estetik saat dibaca di Excel
        $jenis = ($row['category_type'] == 'income') ? 'Pemasukan' : 'Pengeluaran';
        
        fputcsv($output, [
            $row['date'],
            $row['category_name'],
            $jenis,
            $row['description'],
            $row['amount']
        ]);
    }

    fclose($output);
    exit();

} catch (PDOException $e) {
    die("Gagal mengekspor data: " . $e->getMessage());
}