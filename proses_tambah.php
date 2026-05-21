<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    try {
        $sql = "INSERT INTO transactions (category_id, amount, description, date) 
                VALUES (:category_id, :amount, :description, :date)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $category_id,
            ':amount'      => $amount,
            ':description' => $description,
            ':date'        => $date
        ]);

        // Redirect kembali ke index setelah sukses
        header("Location: index.php?status=success");
        exit();

    } catch (PDOException $e) {
        die("Gagal menyimpan data: " . $e->getMessage());
    }
}