<?php
/**
 * OMAH BELUMBUNGAN - Login Administrator
 */
require_once dirname(__DIR__) . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Nama pengguna dan kata sandi wajib diisi.';
    } elseif (login_user($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nama pengguna atau kata sandi tidak cocok.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk Pengelola - Omah Belumbungan</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: radial-gradient(circle at 50% 50%, #FAF6EE 0%, #E8DFC8 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2B2623;
    }
    .login-card {
      background: #FFFFFF;
      width: 90%;
      max-width: 420px;
      border-radius: 16px;
      padding: 40px 32px;
      box-shadow: 0 16px 40px rgba(58, 42, 29, 0.12);
      border: 1px solid #E2DACB;
    }
    .brand-header {
      text-align: center;
      margin-bottom: 28px;
    }
    .logo-badge {
      width: 52px;
      height: 52px;
      background: #A84A29;
      color: #FFF;
      font-family: 'Cinzel', serif;
      font-weight: bold;
      font-size: 1.5rem;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
      box-shadow: 0 4px 14px rgba(168, 74, 41, 0.3);
    }
    .brand-header h1 {
      font-family: 'Cinzel', serif;
      font-size: 1.35rem;
      color: #3A2A1D;
      margin-bottom: 4px;
    }
    .brand-header p {
      font-size: 0.82rem;
      color: #736C65;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 6px;
      color: #4A3E36;
    }
    input {
      width: 100%;
      box-sizing: border-box;
      padding: 12px 14px;
      border-radius: 8px;
      border: 1px solid #D6CCBC;
      font-size: 0.95rem;
      font-family: inherit;
      transition: border-color 0.2s;
    }
    input:focus {
      outline: none;
      border-color: #A84A29;
      box-shadow: 0 0 0 3px rgba(168, 74, 41, 0.12);
    }
    .btn-submit {
      width: 100%;
      background: #A84A29;
      color: #FFF;
      border: none;
      padding: 13px;
      border-radius: 8px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      margin-top: 10px;
    }
    .btn-submit:hover {
      background: #87361A;
    }
    .error-box {
      background: #FFEBEE;
      color: #C62828;
      border: 1px solid #FFCDD2;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-bottom: 20px;
      text-align: center;
    }
    .help-hint {
      margin-top: 24px;
      text-align: center;
      font-size: 0.8rem;
      color: #8C847C;
      background: #FAF7F2;
      padding: 10px;
      border-radius: 6px;
    }
    .back-home {
      display: block;
      text-align: center;
      margin-top: 20px;
      font-size: 0.85rem;
      color: #A84A29;
      text-decoration: none;
    }
    .back-home:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="login-card">
  <div class="brand-header">
    <div class="logo-badge" style="background: transparent; box-shadow: none;">
      <img src="../assets/images/LogoOmah.png" alt="OB" style="width: 54px; height: 49px; object-fit: contain;">
    </div>
    <h1>Omah Belumbungan</h1>
    <p>Panel Masuk Pengelola & Administrasi</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form action="login.php" method="POST">
    <div class="form-group">
      <label for="username">Nama Pengguna (Username)</label>
      <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
    </div>

    <div class="form-group">
      <label for="password">Kata Sandi (Password)</label>
      <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
    </div>

    <button type="submit" class="btn-submit">Masuk ke Panel Pengelola &rarr;</button>
  </form>

  <div class="help-hint">
    <strong>Kredensial Awal:</strong> Username: <code>admin</code> &bull; Password: <code>admin123</code>
  </div>

  <a href="../index.php" class="back-home">&larr; Kembali ke Website Utama</a>
</div>

</body>
</html>
