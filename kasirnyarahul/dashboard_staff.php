<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("SELECT SUM(total_harga) as total FROM penjualan WHERE DATE(tanggal_penjualan) = CURRENT_DATE()");
$today_sales = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT SUM(total_harga) as total FROM penjualan WHERE MONTH(tanggal_penjualan) = MONTH(CURRENT_DATE())");
$month_sales = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("
    SELECT p.tanggal_penjualan, pl.nama_pelanggan, p.total_harga 
    FROM penjualan p
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    ORDER BY p.tanggal_penjualan DESC LIMIT 5
");
$recent_sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .content-wrapper {
            margin-left: var(--sidebar-width);
            padding: 30px;
            background: #f0f2f5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #1a237e, #283593);
            padding: 35px;
            border-radius: 25px;
            min-width: 250px;
            color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-title i {
            font-size: 24px;
        }

        .stat-value {
            color: white;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .recent-sales {
            background: white;
            padding: 35px;
            border-radius: 25px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .recent-sales h2 {
            color: #1a237e;
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sales-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .sales-table th {
            color: #1a237e;
            font-weight: 600;
            font-size: 16px;
            padding: 20px;
            background: #f8f9fa;
        }

        .sales-table td {
            padding: 20px;
            font-size: 16px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        }

        .sales-table tr td:first-child {
            border-radius: 15px 0 0 15px;
        }

        .sales-table tr td:last-child {
            border-radius: 0 15px 15px 0;
        }

        .sales-table tr:hover td {
            background: #f8f9fa;
            transform: scale(1.01);
            transition: all 0.3s ease;
        }

        .amount {
            font-weight: 600;
            color: #1a237e;
        }

        .date {
            color: #666;
        }

        .customer-name {
            color: #283593;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">
                    <i class='bx bx-money-withdraw'></i>
                    Today's Sales
                </div>
                <div class="stat-value">Rp <?= number_format($today_sales) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">
                    <i class='bx bx-calendar-check'></i>
                    This Month's Sales
                </div>
                <div class="stat-value">Rp <?= number_format($month_sales) ?></div>
            </div>
        </div>

        <div class="recent-sales">
            <h2>
                <i class='bx bx-receipt'></i>
                Recent Sales
            </h2>
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td class="date"><?= date('d M Y H:i', strtotime($sale['tanggal_penjualan'])) ?></td>
                        <td class="customer-name"><?= htmlspecialchars($sale['nama_pelanggan']) ?></td>
                        <td class="amount">Rp <?= number_format($sale['total_harga']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
