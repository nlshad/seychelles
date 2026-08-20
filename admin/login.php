<?php
/**
 * Seychelles International Cargo LLC - Admin Login Page
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_user_id']  = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Seychelles International Cargo LLC</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <style>
    :root {
      --bg-gradient: linear-gradient(135deg, #0A192F 0%, #0F2A4A 100%);
      --color-primary: #0066FF;
      --color-accent: #00E5FF;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg-gradient);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      color: #FFFFFF;
    }
    .login-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .login-brand {
      font-family: 'Outfit', sans-serif;
      font-weight: 800;
      font-size: 1.5rem;
      text-align: center;
      margin-bottom: 0.5rem;
    }
    .login-brand span { color: var(--color-accent); }
    .login-subtitle {
      text-align: center;
      color: #94A3B8;
      font-size: 0.9rem;
      margin-bottom: 2rem;
    }
    .form-group {
      margin-bottom: 1.25rem;
    }
    .form-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #CBD5E1;
    }
    .form-control {
      width: 100%;
      padding: 0.85rem 1rem;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      color: #FFFFFF;
      font-size: 0.95rem;
      outline: none;
      transition: all 0.2s;
    }
    .form-control:focus {
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.3);
    }
    .btn-submit {
      width: 100%;
      padding: 0.85rem;
      background: var(--color-primary);
      color: #FFFFFF;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 1rem;
      transition: all 0.2s;
    }
    .btn-submit:hover {
      background: #0052CC;
      transform: translateY(-2px);
    }
    .alert-danger {
      background: rgba(239, 68, 68, 0.2);
      border: 1px solid #EF4444;
      color: #FCA5A5;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-bottom: 1.5rem;
    }
    .default-info {
      background: rgba(0, 229, 255, 0.1);
      border: 1px dashed var(--color-accent);
      padding: 0.75rem;
      border-radius: 8px;
      font-size: 0.8rem;
      color: #93EBF3;
      margin-top: 1.5rem;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-brand" style="text-align:center;">
    <img src="../images/logo.gif" alt="Seychelles International Cargo LLC" style="max-height:60px; background:#FFFFFF; padding:6px 14px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
  </div>
  <p class="login-subtitle">Administrator Portal Access</p>

  <?php if (!empty($error)): ?>
    <div class="alert-danger">
      <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form action="login.php" method="POST">
    <div class="form-group">
      <label class="form-label"><i class="fa-solid fa-user me-1"></i> Username</label>
      <input type="text" name="username" class="form-control" placeholder="Enter admin username" required autofocus>
    </div>

    <div class="form-group">
      <label class="form-label"><i class="fa-solid fa-lock me-1"></i> Password</label>
      <input type="password" name="password" class="form-control" placeholder="Enter password" required>
    </div>

    <button type="submit" class="btn-submit">
      <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
    </button>
  </form>

  <div class="default-info">
    <i class="fa-solid fa-key me-1"></i> Initial Login: <strong>admin</strong> / <strong>Admin@Seychelles2026!</strong>
  </div>
</div>

</body>
</html>
