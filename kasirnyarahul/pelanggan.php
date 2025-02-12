<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle Delete
if (isset($_POST['delete_customer'])) {
    $id = $_POST['id_pelanggan'];
    try {
        // First delete related records in penjualan table
        $stmt = $pdo->prepare("DELETE FROM penjualan WHERE id_pelanggan = ?");
        $stmt->execute([$id]);
        
        // Then delete the customer
        $stmt = $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
        $stmt->execute([$id]);
    } catch(PDOException $e) {
        echo "<script>alert('Cannot delete customer with existing sales records');</script>";
    }
}


// Handle Edit
if (isset($_POST['edit_customer'])) {
    $id = $_POST['id_pelanggan'];
    $nama = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    
    $stmt = $pdo->prepare("UPDATE pelanggan SET nama_pelanggan = ?, alamat = ?, no_telp = ? WHERE id_pelanggan = ?");
    $stmt->execute([$nama, $alamat, $no_telp, $id]);
}

// Add Customer
if (isset($_POST['add_customer'])) {
    $nama = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    
    $stmt = $pdo->prepare("INSERT INTO pelanggan (nama_pelanggan, alamat, no_telp) VALUES (?, ?, ?)");
    $stmt->execute([$nama, $alamat, $no_telp]);
}

// Fetch Customers
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY created_at DESC");
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Management</title>
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
                <h2>Customer Management</h2>
                <button class="btn" onclick="openModal('addModal')">Add New Customer</button>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['id_pelanggan'] ?></td>
                                <td><?= htmlspecialchars($customer['nama_pelanggan']) ?></td>
                                <td><?= htmlspecialchars($customer['alamat']) ?></td>
                                <td><?= htmlspecialchars($customer['no_telp']) ?></td>
                                <td>
                                    <button class="btn" onclick='editCustomer(<?= json_encode($customer) ?>)'>Edit</button>
                                    <button class="btn" onclick="deleteCustomer(<?= $customer['id_pelanggan'] ?>)">Delete</button>
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
                <h3>Add New Customer</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="nama_pelanggan" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="alamat" class="form-input" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="no_telp" class="form-input" required>
                    </div>
                    <button type="submit" name="add_customer" class="btn">Add Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Customer</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Form will be inserted here -->
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

        function editCustomer(customer) {
            let form = document.createElement('form');
            form.method = 'POST';
            
            form.innerHTML = `
                <input type="hidden" name="id_pelanggan" value="${customer.id_pelanggan}">
                <div class="form-group">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="nama_pelanggan" value="${customer.nama_pelanggan}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="alamat" class="form-input" required>${customer.alamat}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="no_telp" value="${customer.no_telp}" class="form-input" required>
                </div>
                <button type="submit" name="edit_customer" class="btn">Save Changes</button>
            `;
            
            const modalBody = document.querySelector('#editModal .modal-body');
            modalBody.innerHTML = '';
            modalBody.appendChild(form);
            openModal('editModal');
        }

        function deleteCustomer(id) {
            if (confirm('Are you sure you want to delete this customer?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="id_pelanggan" value="${id}">
                    <input type="hidden" name="delete_customer" value="1">
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
