<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

$ventasHoy = [];
$totalHoy = 0.0;
$stockBajo = [];

try {
    $stmtVentas = $pdo->query('SELECT v.id, v.qty, v.total, v.fecha, p.nombre FROM ventas v INNER JOIN productos p ON p.id = v.producto_id WHERE DATE(v.fecha) = DATE("now", "localtime") ORDER BY v.fecha DESC');
    $ventasHoy = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

    $stmtTotal = $pdo->prepare('SELECT IFNULL(SUM(total), 0) FROM ventas WHERE DATE(fecha) = DATE("now", "localtime")');
    $stmtTotal->execute();
    $totalHoy = (float) $stmtTotal->fetchColumn();

    $stockStmt = $pdo->query('SELECT * FROM productos WHERE stock < 10 ORDER BY stock ASC');
    $stockBajo = $stockStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ventasHoy = [];
    $stockBajo = [];
    $totalHoy = 0.0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cierre_z') {
    $message = 'Cierre Z generado. Total del día: $' . number_format($totalHoy, 2, ',', '.');
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Web - Reportes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div>
                <h1>Reportes</h1>
                <p>Resumen de ventas diarias y stock crítico.</p>
            </div>
            <a class="btn btn-primary" href="dashboard.php">Volver</a>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <section class="grid" style="margin-bottom: 32px;">
            <div class="card">
                <h2>Ventas de hoy</h2>
                <p class="lead" style="color:#10b981;">$<?php echo number_format($totalHoy, 2, ',', '.'); ?></p>
                <div class="list">
                    <?php foreach ($ventasHoy as $venta): ?>
                        <div class="list-item">
                            <div>
                                <strong><?php echo htmlspecialchars($venta['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <div><?php echo $venta['qty']; ?> uds · <?php echo date('H:i', strtotime($venta['fecha'])); ?></div>
                            </div>
                            <span class="badge badge-success">$<?php echo number_format($venta['total'], 2, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($ventasHoy)): ?>
                        <p>No hay ventas hoy.</p>
                    <?php endif; ?>
                </div>
                <form method="post" style="margin-top:24px;">
                    <input type="hidden" name="action" value="cierre_z">
                    <button type="submit" class="btn btn-success">Generar Cierre Z</button>
                </form>
            </div>

            <div class="card">
                <h2>Stock bajo</h2>
                <div class="list">
                    <?php foreach ($stockBajo as $producto): ?>
                        <div class="list-item">
                            <strong><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span class="badge badge-danger">Stock: <?php echo $producto['stock']; ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($stockBajo)): ?>
                        <p>Todos los productos tienen stock suficiente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
