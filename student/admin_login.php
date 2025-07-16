<?php
session_start();
$conn = new mysqli("localhost", "root", "", "student_registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Automatically create default admin if not exists
$defaultUsername = "admin";
$defaultPassword = password_hash("admin123", PASSWORD_DEFAULT);
$res = $conn->query("SELECT * FROM admin WHERE username = '$defaultUsername'");
if ($res->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $defaultUsername, $defaultPassword);
    $stmt->execute();
}

// 🔒 Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

// 🚫 Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: register.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: register.php");
            exit;
        } else {
            $error = "❌ Invalid password.";
        }
    } else {
        $error = "❌ Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <meta charset="UTF-8" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }

        .login-card h3 {
            font-weight: 700;
            color: #333;
        }

        .form-label {
            font-weight: 600;
            color: #444;
        }

        .btn-primary {
            background-color: #667eea;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5a67d8;
        }

        .icon-box {
            font-size: 3rem;
            color: #667eea;
            text-align: center;
        }

        .alert {
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="icon-box mb-3">
            🔐
        </div>
        <h3 class="text-center mb-3">Admin Login</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">👤 Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required />
            </div>
            <div class="mb-3">
                <label class="form-label">🔑 Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required />
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
