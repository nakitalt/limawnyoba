<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Sistem Pendaftaran Seminar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-color: #ffb6b9;        /* Soft pink */
        --secondary-color: #cdb4db;      /* Light purple */
        --tertiary-color: #a2d2ff;       /* Light blue */
        --background-color: #fef6e4;     /* Warm beige */
        --surface-color: #ffffff;        /* White surface */
        --text-primary: #333;
        --text-secondary: rgba(0, 0, 0, 0.6);
        --transition: all 0.3s ease;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Poppins', sans-serif;
        display: flex;
        min-height: 100vh;
        background: var(--background-color);
        color: var(--text-primary);
    }

    .split-container {
        display: flex;
        width: 100%;
    }

    .left-panel {
        flex: 1;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        text-align: center;
    }

    .left-panel h1 {
        font-size: 2em;
        margin-top: 20px;
    }

    .left-panel p {
        margin-top: 10px;
        font-size: 1em;
        opacity: 0.9;
    }

    .left-panel img {
        max-width: 80%;
        height: auto;
    }

    .right-panel {
        flex: 1;
        background: var(--surface-color);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-box {
        width: 100%;
        max-width: 400px;
        animation: fadeSlideIn 0.8s ease-out forwards;
        opacity: 0;
    }

    .login-box h2 {
        text-align: center;
        margin-bottom: 25px;
        color: var(--primary-color);
    }

    .form-group {
        margin-bottom: 20px;
        position: relative;
    }

    .form-group i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--secondary-color);
    }

    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 12px 12px 12px 38px;
        border: 2px solid #ccc;
        border-radius: 10px;
        font-size: 1em;
        background: #f9f9f9;
        color: #333;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus, input[type="password"]:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    .btn:hover {
        background: var(--tertiary-color);
        transform: translateY(-2px);
    }

    .error {
        background: rgba(255, 50, 50, 0.1);
        color: #ef5350;
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid var(--primary-color);
    }

    @media screen and (max-width: 768px) {
        .split-container {
            flex-direction: column;
        }

        .left-panel img {
            max-width: 60%;
        }
    }
    </style>
</head>
<body>
    <div class="split-container">
        <div class="left-panel">
            <img src="login.png" alt="Seminar Illustration" />
            <h1>Sistem Pendaftaran Seminar</h1>
            <p>Selamat datang! Silakan login untuk mengelola pendaftaran seminar Anda.</p>
        </div>
        <div class="right-panel">
            <div class="login-box">
                <h2><i class="fas fa-sign-in-alt"></i> Login</h2>

                <?php if ($error): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Auto focus on username field
    document.querySelector("input[name='username']").focus();
    
    // Form validation
    const form = document.querySelector("form");
    form.addEventListener("submit", function(e) {
        const username = document.querySelector("input[name='username']").value.trim();
        const password = document.querySelector("input[name='password']").value.trim();
        
        if (!username || !password) {
            e.preventDefault();
            alert("Silakan isi username dan password!");
        }
    });
});
</script>
</html>