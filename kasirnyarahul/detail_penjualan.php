<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        pel.nama_pelanggan,
        p.total_harga,
        GROUP_CONCAT(
            CONCAT(
                pr.nama_produk,
                ' (',
                dp.jumlah,
                ' x Rp ',
                FORMAT(dp.subtotal/dp.jumlah, 2),
                ')'
            )
            SEPARATOR '<br>'
        ) as items
    FROM penjualan p
    LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
    LEFT JOIN produk pr ON dp.id_produk = pr.id_produk
    GROUP BY p.id_penjualan, p.tanggal_penjualan, pel.nama_pelanggan, p.total_harga
    ORDER BY p.tanggal_penjualan DESC
");


$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .search-box {
            margin-bottom: 20px;
            padding: 8px;
            width: 100%;
            max-width: 300px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .summary-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .items-column {
            max-width: 300px;
            white-space: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header">
                <h2>Sales History</h2>
            </div>
            <div class="card-body">
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Sales</h3>
                        <p>Rp <?= number_format(array_sum(array_column($sales, 'total_harga')), 2) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Transactions</h3>
                        <p><?= count($sales) ?></p>
                    </div>
                </div>

                <input type="text" id="searchInput" class="search-box" placeholder="Search transactions...">
                
                <div class="table-container">
                    <table id="salesTable">
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td>#<?= str_pad($sale['id_penjualan'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($sale['tanggal_penjualan'])) ?></td>
                                <td><?= htmlspecialchars($sale['nama_pelanggan']) ?></td>
                                <td class="items-column"><?= $sale['items'] ?></td>
                                <td>Rp <?= number_format($sale['total_harga'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#salesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
 