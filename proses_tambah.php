<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']); 
    $category_type = trim($_POST['category_type']); 
    $date          = $_POST['date'];
    $amount        = $_POST['amount'];
    $description   = trim($_POST['description']);

    if (empty($category_name) || empty($amount)) {
        die("Data tidak boleh kosong!");
    }

    try {
        // Cek kategori (abaikan huruf besar/kecil & spasi)
        $check_sql = "SELECT id FROM categories WHERE LOWER(TRIM(name)) = LOWER(:name) AND LOWER(type) = LOWER(:type)";
        $stmt_check = $pdo->prepare($check_sql);
        $stmt_check->execute([
            ':name' => $category_name,
            ':type' => $category_type
        ]);
        $category = $stmt_check->fetch();

        if ($category) {
            $category_id = $category['id'];
        } else {
            // Jika baru, insert ke tabel categories
            $insert_cat = "INSERT INTO categories (name, type) VALUES (:name, :type)";
            $stmt_insert_cat = $pdo->prepare($insert_cat);
            $stmt_insert_cat->execute([
                ':name' => $category_name,
                ':type' => $category_type
            ]);
            $category_id = $pdo->lastInsertId();
        }

        // Insert ke tabel transactions
        $insert_tr = "INSERT INTO transactions (category_id, amount, description, date) 
                      VALUES (:category_id, :amount, :description, :date)";
        $stmt_tr = $pdo->prepare($insert_tr);
        $stmt_tr->execute([
            ':category_id' => $category_id,
            ':amount'      => $amount,
            ':description' => $description,
            ':date'        => $date
        ]);

        header("Location: index.php?status=success");
        exit();

    } catch (PDOException $e) {
        die("Terjadi kesalahan database: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}