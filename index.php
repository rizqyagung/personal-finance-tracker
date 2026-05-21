<?php
require_once 'config/database.php';

// 1. Ambil semua transaksi asli dari database
$query = "SELECT transactions.*, categories.name as category_name, categories.type 
          FROM transactions 
          JOIN categories ON transactions.category_id = categories.id 
          ORDER BY date DESC";
$stmt = $pdo->query($query);
$transactions = $stmt->fetchAll();

// 2. Logika perhitungan saldo
$total_income = 0;
$total_expense = 0;

foreach ($transactions as $tr) {
    if (strtolower($tr['type']) == 'income') {
        $total_income += $tr['amount'];
    } else {
        $total_expense += $tr['amount'];
    }
}
$total_balance = $total_income - $total_expense;

// Query untuk mengambil total pengeluaran per kategori
$query_chart = "SELECT categories.name as category_name, SUM(transactions.amount) as total 
                FROM transactions 
                JOIN categories ON transactions.category_id = categories.id 
                WHERE LOWER(categories.type) = 'expense'
                GROUP BY categories.id";
$stmt_chart = $pdo->query($query_chart);
$chart_data = $stmt_chart->fetchAll();

// Siapkan array kosong untuk menampung data yang akan dibaca oleh JavaScript
$chart_labels = [];
$chart_totals = [];

foreach ($chart_data as $data) {
    $chart_labels[] = $data['category_name'];
    $chart_totals[] = $data['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Tracker | My Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-blue-600 p-4 text-white shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">💰 MyFinance</h1>
            <div>
                <a href="index.php" class="px-4 hover:underline">Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto mt-10 px-4">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Total Saldo</p>
                <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_balance, 0, ',', '.'); ?></h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Total Pemasukan</p>
                <h3 class="text-2xl font-bold text-green-600">Rp <?= number_format($total_income, 0, ',', '.'); ?></h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-red-500">
                <p class="text-gray-500 text-sm">Total Pengeluaran</p>
                <h3 class="text-2xl font-bold text-red-600">Rp <?= number_format($total_expense, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Transaksi Terakhir</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2 bg-white rounded-xl shadow-md overflow-hidden h-fit">
        </div>

    <div class="bg-white rounded-xl shadow-md p-6 h-fit">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Analisis Pengeluaran</h2>
        <canvas id="expenseChart" width="100" height="100"></canvas>
    </div>

</div>
                <!-- PERBAIKAN: Tambah onclick di bawah ini -->
                <button onclick="toggleModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    + Tambah Transaksi
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
    <tr>
        <th class="p-4 text-gray-600 font-medium text-sm">Tanggal</th>
        <th class="p-4 text-gray-600 font-medium text-sm">Kategori</th>
        <th class="p-4 text-gray-600 font-medium text-sm">Keterangan</th>
        <th class="p-4 text-gray-600 font-medium text-sm">Nominal</th>
        <th class="p-4 text-gray-600 font-medium text-sm text-center">Aksi</th> </tr>
</thead>
                    <tbody class="divide-y divide-gray-100">
    <?php if (empty($transactions)): ?>
        <tr><td colspan="5" class="p-4 text-center text-gray-500 italic">Belum ada data transaksi.</td></tr>
    <?php else: ?>
        <?php foreach ($transactions as $tr): ?>
<tr>
    <td class="p-4 text-sm text-gray-600"><?= $tr['date']; ?></td>
    <td class="p-4">
        <span class="px-2 py-1 <?= strtolower($tr['type']) == 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md text-xs">
            <?= $tr['category_name']; ?>
        </span>
    </td>
    <td class="p-4 text-sm text-gray-600"><?= htmlspecialchars($tr['description']); ?></td>
    <td class="p-4 text-sm font-semibold <?= strtolower($tr['type']) == 'income' ? 'text-green-600' : 'text-red-600'; ?>">
        <?= (strtolower($tr['type']) == 'income' ? '+ ' : '- ') . "Rp " . number_format($tr['amount'], 0, ',', '.'); ?>
    </td>
    <td class="p-4 text-center">
        <a href="proses_hapus.php?id=<?= $tr['id']; ?>" 
           onclick="return confirm('Apakah kamu yakin ingin menghapus transaksi ini?');" 
           class="text-red-500 hover:text-red-700 font-medium text-sm transition">
            Hapus
        </a>
    </td>
</tr>
<?php endforeach; ?>
    <?php endif; ?>
</tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div id="transactionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Tambah Transaksi</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form action="proses_tambah.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="date" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php
                        $stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                        while($cat = $stmt_cat->fetch()) {
                            // KODE BARU (Lebih aman dari perbedaan huruf besar/kecil)
echo "<option value='{$cat['id']}'>{$cat['name']} (" . (strtolower($cat['type']) == 'income' ? 'Masuk' : 'Keluar') . ")</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                    <input type="number" name="amount" placeholder="50000" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="description" rows="2" placeholder="Detail transaksi..." class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 shadow-md transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.toggle('hidden');
        }
        window.onclick = function(event) {
            const modal = document.getElementById('transactionModal');
            if (event.target == modal) toggleModal();
        }
        // Ambil data dari PHP menggunakan json_encode
const chartLabels = <?= json_encode($chart_labels); ?>;
const chartTotals = <?= json_encode($chart_totals); ?>;

const ctx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'doughnut', // Model donat (variasi dari pie chart) yang modern
    data: {
        labels: chartLabels,
        datasets: [{
            data: chartTotals,
            backgroundColor: [
                '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
    </script>
</body>
</html>