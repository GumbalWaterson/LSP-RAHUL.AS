<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction'])) {
    $transaction = json_decode($_POST['transaction'], true);
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO penjualan (id_pelanggan, tanggal_penjualan, total_harga) VALUES (?, ?, ?)");
        $stmt->execute([$transaction['customer_id'], $transaction['transaction_date'], $transaction['total']]);
        
        $penjualan_id = $pdo->lastInsertId();
        
        foreach ($transaction['items'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)");
            $stmt->execute([$penjualan_id, $item['id_produk'], $item['quantity'], $item['subtotal']]);
            
            $stmt = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
            $stmt->execute([$item['quantity'], $item['id_produk']]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM produk WHERE stok > 0");
$products = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM pelanggan");
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Transaction</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .transaction-container {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
        padding: 30px;
        min-height: calc(100vh - 100px);
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        padding: 20px;
    }

    .product-card {
        border: 1px solid #ddd;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        background: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
        min-height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-card h3 {
        font-size: 1.5rem;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .product-card p {
        font-size: 1.2rem;
        margin: 15px 0;
        color: #666;
    }

    .cart-section {
        border: 1px solid #ddd;
        padding: 30px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        min-height: calc(100vh - 160px);
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;
    }

    .cart-item h4 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.2rem;
    }

    .cart-item p {
        margin: 5px 0;
        color: #666;
    }

    .total-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #ddd;
    }

    .form-input {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1.1rem;
    }

    .btn {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .btn:hover {
        background: #34495e;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    h2 {
        color: #2c3e50;
        margin-bottom: 25px;
        font-size: 1.8rem;
    }
</style>

</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="transaction-container">
            <div class="products-section">
                <h2>Products</h2>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <h3><?= htmlspecialchars($product['nama_produk']) ?></h3>
                        <p>Price: Rp <?= number_format($product['harga']) ?></p>
                        <p>Stock: <?= $product['stok'] ?></p>
                        <button class="btn" onclick='addToCart(<?= json_encode($product) ?>)'>Add to Cart</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="cart-section">
                <h2>Shopping Cart</h2>
                <select id="customer" class="form-input">
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id_pelanggan'] ?>"><?= htmlspecialchars($customer['nama_pelanggan']) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="date" id="transaction-date" class="form-input" value="<?= date('Y-m-d') ?>">
                
                <div id="cart-items"></div>
                
                <div class="total-section">
                    <h3>Total: Rp <span id="total-amount">0.00</span></h3>
                    <button class="btn" onclick="processTransaction()">Process Transaction</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        
        function addToCart(product) {
            const existingItem = cart.find(item => item.id_produk === product.id_produk);
            if (existingItem && existingItem.quantity < product.stok) {
                existingItem.quantity++;
                existingItem.subtotal = parseFloat(existingItem.quantity * product.harga);
            } else if (!existingItem && product.stok > 0) {
                cart.push({
                    id_produk: product.id_produk,
                    nama_produk: product.nama_produk,
                    harga: parseFloat(product.harga),
                    quantity: 1,
                    subtotal: parseFloat(product.harga)
                });
            }
            updateCart();
        }
        
        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
        }
        
        function updateCart() {
            const cartDiv = document.getElementById('cart-items');
            let html = '';
            let total = 0;
            
            cart.forEach((item, index) => {
                html += `
                    <div class="cart-item">
                        <div>
                            <h4>${item.nama_produk}</h4>
                            <p>${item.quantity} x Rp ${parseFloat(item.harga).toLocaleString()}</p>
                        </div>
                        <div>
                            <p>Rp ${parseFloat(item.subtotal).toLocaleString()}</p>
                            <button class="btn" onclick="removeItem(${index})">Remove</button>
                        </div>
                    </div>
                `;
                total += parseFloat(item.subtotal);
            });
            
            cartDiv.innerHTML = html;
            document.getElementById('total-amount').textContent = total.toLocaleString();
        }
        
        function processTransaction() {
            const customer = document.getElementById('customer').value;
            const transactionDate = document.getElementById('transaction-date').value;
            
            if (!customer) return alert('Please select a customer');
            if (!cart.length) return alert('Cart is empty');
            if (!transactionDate) return alert('Please select transaction date');
            
            let total = cart.reduce((sum, item) => sum + parseFloat(item.subtotal), 0);
            
            const formData = new FormData();
            formData.append('transaction', JSON.stringify({
                customer_id: customer,
                items: cart,
                total: total,
                transaction_date: transactionDate
            }));
            
            fetch('penjualan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaction successful!');
                    cart = [];
                    updateCart();
                    location.reload();
                } else {
                    alert('Transaction failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error processing transaction');
                console.error(error);
            });
        }
    </script>
</body>
</html>
