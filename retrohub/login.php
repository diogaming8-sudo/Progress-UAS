<?php
require_once 'config.php';

$message = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $message = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - RETRO-HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #ffe3ee; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background: #fff; border: 4px solid #604b79; border-radius: 20px; padding: 30px; box-shadow: 8px 8px 0 #ff5c9a; width: 350px; }
    </style>
</head>
<body>
<div class="login-card">
    <h3 class="text-center fw-bold mb-4" style="color: #ff2f7d;">LOGIN RETRO-HUB</h3>
    <?php if($message): ?>
        <div class="alert alert-danger py-2"><?= $message; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="user / admin" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="123" required>
        </div>
        <button type="submit" name="login" class="btn btn-danger w-100 fw-bold">MASUK</button>
    </form>
</div>
</body>
</html>