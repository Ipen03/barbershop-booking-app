<?php
session_start();

// Jika sudah login admin, langsung ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Akun admin default untuk tugas kuliah
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Gentleman's Barbershop</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1E1E1E 0%, #121212 100%);
            color: #ffffff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Glowing background circles */
        .glowing-circle {
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 193, 7, 0.05);
            border-radius: 50%;
            filter: blur(80px);
            z-index: 1;
        }

        .circle-1 {
            top: -100px;
            right: -100px;
        }

        .circle-2 {
            bottom: -100px;
            left: -100px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            z-index: 2;
            text-align: center;
        }

        .logo-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 60px;
            height: 60px;
            background: rgba(255, 193, 7, 0.1);
            border: 2px solid rgba(255, 193, 7, 0.3);
            border-radius: 50%;
            margin-bottom: 20px;
            color: #ffc107;
            font-size: 28px;
        }

        h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2.0px;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 13px;
            color: #ffc107;
            letter-spacing: 3.0px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 12px;
            color: #aaaaaa;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1.0px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #ffc107;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #ffc107;
            color: #121212;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1.0px;
            margin-top: 15px;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.25);
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 193, 7, 0.35);
        }

        .error-box {
            background: rgba(244, 67, 54, 0.15);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: #ff5252;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        
        .cred-info {
            margin-top: 25px;
            font-size: 11px;
            color: #666666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="glowing-circle circle-1"></div>
    <div class="glowing-circle circle-2"></div>

    <div class="login-container">
        <div class="logo-icon">✂</div>
        <h2>GENTLEMAN'S</h2>
        <div class="subtitle">ADMIN PORTAL</div>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-submit">LOG IN</button>
        </form>

        <div class="cred-info">
            Info Login Default Mahasiswa:<br>
            Username: <b>admin</b> &nbsp;|&nbsp; Password: <b>admin123</b>
        </div>
    </div>
</body>
</html>
