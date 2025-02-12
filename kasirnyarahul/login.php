<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = MD5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header('Location: dashboard_admin.php');
        } else {
            header('Location: dashboard_staff.php');
        }
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right,rgb(39, 75, 122),rgb(26, 74, 119));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px 30px;
            width: 320px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-header h3 {
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
            border-color:rgb(36, 93, 131);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(to right,rgb(39, 75, 122),rgb(26, 74, 119));
            border: none;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            font-size: 15px;
            color: white;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: linear-gradient(to right,rgb(39, 75, 122),rgb(26, 74, 119));
            transform: translateY(-1px);
            transition: all 0.2s;
        }
        .form-label {
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3>Login</h3>
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
            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>
</body>
</html>
