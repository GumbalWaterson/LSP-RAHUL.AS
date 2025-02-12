<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $role = $_POST['role'];
   
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$username, $password, $role]);
        $_SESSION['success_message'] = "User telah ditambahkan!";
        header('Location: register.php');
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #2c3e50, #34495e);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px 30px;
            width: 320px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-header h3 {
            font-size: 22px;
            color: #444;
            margin-bottom: 0;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-register {
            background: linear-gradient(180deg, #2c3e50, #34495e);
            border: none;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            font-size: 15px;
            color: white;
            margin-top: 10px;
        }

        .btn-register:hover {
            background: linear-gradient(180deg, #2c3e50, #34495e);
            transform: translateY(-1px);
            transition: all 0.2s;
        }

        .btn-back {
            background: #34495e;
            border: none;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            font-size: 15px;
            color: white;
            margin-top: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-back:hover {
            background: #2c3e50;
            color: white;
            transform: translateY(-1px);
            transition: all 0.2s;
        }

        .form-label {
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }

        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23555' viewBox='0 0 16 16'%3E%3Cpath d='M8 11.5l-5-5h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h3>Register New User</h3>
        </div>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success py-2" role="alert">
        <?php 
        echo $_SESSION['success_message']; 
        unset($_SESSION['success_message']);
        ?>
    </div>
<?php endif; ?>

            <button type="submit" class="btn btn-register">Register</button>
        </form>
        <a href="dashboard_admin.php" class="btn-back">Back to Dashboard</a>
    </div>
</body>
</html>
