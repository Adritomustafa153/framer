<?php
// admin/login.php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

// Handle password hashing setup (remove this after first use)
if (isset($_GET['hash']) && $_GET['hash'] == 'setup') {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Hash the existing password
    $plain_password = 'admin12345678';
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    
    // Update the database
    $query = "UPDATE users SET password_hash = :hash WHERE username = 'Admintest'";
    $stmt = $db->prepare($query);
    $stmt->execute([':hash' => $hashed_password]);
    
    $success = "Password has been hashed successfully! You can now login with username 'Admintest' and password 'admin12345678'";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Get user from database
    $query = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':username' => $username,
        ':email' => $username
    ]);
    
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Check if password is already hashed or plain text
        $password_valid = false;
        
        // Try password_verify first (for hashed passwords)
        if (password_verify($password, $user['password_hash'])) {
            $password_valid = true;
        }
        // Fallback to plain text comparison (for old entries)
        elseif ($password === $user['password_hash']) {
            $password_valid = true;
            
            // Upgrade to hashed password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $db->prepare($update);
            $stmt->execute([$hashed, $user['id']]);
        }
        
        if ($password_valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_login'] = date('Y-m-d H:i:s');
            
            // Update last login
            $update = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $db->prepare($update);
            $stmt->execute([$user['id']]);
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Framer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            background: #f4f4f4;
            font-family: 'Inter', sans-serif;
        }
        .login-box { 
            max-width: 400px; 
            margin: 100px auto; 
            padding: 30px; 
            background: white; 
            border: 2px solid #111;
            box-shadow: 10px 10px 0 rgba(0,0,0,0.2);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            height: 80px;
            width: auto;
        }
        .login-logo h2 {
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 10px;
        }
        .btn-login {
            background: black;
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #333;
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-logo">
                <img src="../logo.png" alt="Framer" onerror="this.style.display='none'">
                <h2>FRAMER</h2>
                <p class="text-muted">Admin Panel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-white border-dark">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" name="username" class="form-control border-dark" 
                               placeholder="Enter username" required autofocus
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'Admintest'; ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-white border-dark">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-dark" 
                               placeholder="Enter password" required
                               value="admin12345678">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                </button>
                
                <div class="text-center">
                    <a href="#" class="text-decoration-none text-dark small">Forgot Password?</a>
                </div>
            </form>
            
            <!-- Setup link - remove this after first successful login -->
            <div class="text-center mt-3 pt-3 border-top">
                <a href="?hash=setup" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-gear"></i> Fix Password Hashing (Run Once)
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>