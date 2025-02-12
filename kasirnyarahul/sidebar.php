<style>
    :root {
        --sidebar-width: 250px;
    }
   
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        background: linear-gradient(180deg, #2c3e50, #34495e);
        position: fixed;
        left: 0;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .header-title {
        color: #fff;
        padding: 25px;
        font-size: 18px;
        font-weight: 500;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 15px;
    }

    .nav-link {
        color: #fff;
        padding: 12px 25px;
        transition: all 0.2s;
        text-decoration: none;
        display: block;
    }

    .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
        padding-left: 30px;
    }

    .user-section {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 20px;
        background: rgba(0,0,0,0.1);
        color: #fff;
    }

    .user-profile {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logout-btn {
        color: #ff4444;
        text-decoration: none;
    }

    .logout-btn:hover {
        color: #ff0000;
    }
</style>

<nav class="sidebar">
    <div class="header-title">DASHBOARD KASIR!</div>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="<?= $_SESSION['role'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_staff.php' ?>">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="produk.php">Data Produk</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="penjualan.php">Penjualan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="pelanggan.php">Pelanggan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="detail_penjualan.php">Detail Penjualan</a>
        </li>
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="register.php">Registrasi Akun</a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="user-section">
        <div class="user-profile">
            <span><?= $_SESSION['role'] === 'admin' ? 'Admin' : 'Staff' ?></span>
            <a class="logout-btn" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
