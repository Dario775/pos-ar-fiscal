<?php
require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$loginError = '';
$registerMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            $stmt = $pdo->prepare('SELECT id, username, password FROM usuarios WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $loginError = 'Usuario o contraseña incorrectos.';
            }
        } else {
            $loginError = 'Por favor complete todos los campos.';
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST['new_username'] ?? '');
        $password = $_POST['new_password'] ?? '';

        if ($username && $password) {
            try {
                $stmt = $pdo->prepare('INSERT INTO usuarios (username, password) VALUES (:username, :password)');
                $stmt->execute([
                    ':username' => $username,
                    ':password' => password_hash($password, PASSWORD_DEFAULT)
                ]);
                $registerMessage = 'Usuario registrado correctamente. Ya puede iniciar sesión.';
            } catch (PDOException $e) {
                $loginError = 'No se pudo registrar el usuario. Elija otro nombre.';
            }
        } else {
            $loginError = 'Por favor complete todos los campos para registrarse.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Web - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container login-wrapper">
        <div class="card login-card">
            <h1>POS Web</h1>
            <?php if ($loginError): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($registerMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <div>
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Iniciar sesión</button>
            </form>
            <hr>
            <form method="post">
                <input type="hidden" name="action" value="register">
                <div>
                    <label for="new_username">Nuevo usuario</label>
                    <input type="text" id="new_username" name="new_username" required>
                </div>
                <div>
                    <label for="new_password">Nueva contraseña</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-success">Registrarse</button>
            </form>
        </div>
    </div>
</body>
</html>
