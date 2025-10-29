<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$totalProductos = 0;
$stockBajo = 0;
$ventasHoy = 0.0;

try {
    $totalProductos = (int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    $stockBajo = (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE stock < 10')->fetchColumn();

    $ventasStmt = $pdo->prepare('SELECT IFNULL(SUM(total), 0) FROM ventas WHERE DATE(fecha) = DATE("now", "localtime")');
    $ventasStmt->execute();
    $ventasHoy = (float) $ventasStmt->fetchColumn();
} catch (PDOException $e) {
    $ventasHoy = 0.0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Web - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div>
                <h1>Panel Principal</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>.</p>
            </div>
            <a class="btn btn-danger" href="logout.php">Cerrar sesión</a>
        </header>

        <section class="grid" style="margin-bottom: 32px;">
            <div class="card">
                <h2>Productos registrados</h2>
                <p class="lead" style="color:#1a56db;"><?php echo $totalProductos; ?></p>
            </div>
            <div class="card">
                <h2>Stock bajo</h2>
                <p class="lead" style="color:#ef4444;"><?php echo $stockBajo; ?></p>
            </div>
            <div class="card">
                <h2>Ventas del día</h2>
                <p class="lead" style="color:#10b981;">$<?php echo number_format($ventasHoy, 2, ',', '.'); ?></p>
            </div>
        </section>

        <section class="grid">
            <a class="card" href="stock.php" style="color:#1a56db;">
                <h2>Gestión de Stock</h2>
                <p>Administre productos, precios y existencias.</p>
            </a>
            <a class="card" href="ventas.php" style="color:#10b981;">
                <h2>Registrar Ventas</h2>
                <p>Cargue ventas, controle stock e impuestos.</p>
            </a>
            <a class="card" href="reportes.php" style="color:#f97316;">
                <h2>Reportes</h2>
                <p>Consulte ventas del día y stock crítico.</p>
            </a>
        </section>
    </div>
</body>
</html>
