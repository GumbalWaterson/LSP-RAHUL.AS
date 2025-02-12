<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// untuk del
if (isset($_POST['delete_product'])) {
    $id = $_POST['id_produk'];
    try {
      
        $stmt = $pdo->prepare("DELETE FROM detail_penjualan WHERE id_produk = ?");
        $stmt->execute([$id]);
        
        // ngapus produk
        $stmt = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
        $stmt->execute([$id]);
    } catch(PDOException $e) {
        echo "<script>alert('Cannot delete product with existing sales records');</script>";
    }
}


// edit
if (isset($_POST['edit_product'])) {
    $id = $_POST['id_produk'];
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    $stmt = $pdo->prepare("UPDATE produk SET nama_produk = ?, harga = ?, stok = ? WHERE id_produk = ?");
    $stmt->execute([$nama, $harga, $stok, $id]);
}

// Add 
if (isset($_POST['add_product'])) {
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, harga, stok) VALUES (?, ?, ?)");
    $stmt->execute([$nama_produk, $harga, $stok]);
}

// produk
$stmt = $pdo->query("SELECT * FROM produk ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .content-wrapper {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 20px;
        }
        
        .card-header {
            background: linear-gradient(to right, #2c3e50, #34495e);
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #2c3e50;
            color: white;
        }
        
        .btn:hover {
            background: #34495e;
        }
        
        .table-container {
            overflow-x: auto;
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
            background: #f8f9fa;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            border-radius: 8px;
            position: relative;
        }
        
        .modal-header {
            padding: 15px 20px;
            background: #2c3e50;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header">
                <h2>Product Management</h2>
                <button class="btn" onclick="openModal('addModal')">Add New Product</button>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id_produk'] ?></td>
                                <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                <td>Rp <?= number_format($product['harga'], 2) ?></td>
                                <td><?= $product['stok'] ?></td>
                                <td>
                                    <button class="btn" onclick='editProduct(<?= json_encode($product) ?>)'>Edit</button>
                                    <button class="btn" onclick="deleteProduct(<?= $product['id_produk'] ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Product</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="nama_produk" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <input type="number" name="harga" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stok" class="form-input" required>
                    </div>
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Product</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editProduct(product) {
            let form = document.createElement('form');
            form.method = 'POST';
            
            form.innerHTML = `
                <input type="hidden" name="id_produk" value="${product.id_produk}">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="nama_produk" value="${product.nama_produk}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <input type="number" name="harga" value="${product.harga}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stok" value="${product.stok}" class="form-input" required>
                </div>
                <button type="submit" name="edit_product" class="btn">Save Changes</button>
            `;
            
            const modalBody = document.querySelector('#editModal .modal-body');
            modalBody.innerHTML = '';
            modalBody.appendChild(form);
            openModal('editModal');
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="id_produk" value="${id}">
                    <input type="hidden" name="delete_product" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
