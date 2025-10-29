<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

try {
    $productos = $pdo->query('SELECT * FROM productos ORDER BY nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sell') {
    $productoId = (int) ($_POST['producto_id'] ?? 0);
    $cantidad = (int) ($_POST['cantidad'] ?? 0);

    if ($productoId && $cantidad > 0) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = :id');
            $stmt->execute([':id' => $productoId]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                if ($producto['stock'] >= $cantidad) {
                    $subtotal = $producto['precio'] * $cantidad;
                    $iva = $subtotal * 0.21;
                    $total = $subtotal + $iva;

                    $pdo->beginTransaction();

                    $ventaStmt = $pdo->prepare('INSERT INTO ventas (producto_id, qty, total, usuario_id) VALUES (:producto_id, :qty, :total, :usuario_id)');
                    $ventaStmt->execute([
                        ':producto_id' => $productoId,
                        ':qty' => $cantidad,
                        ':total' => $total,
                        ':usuario_id' => $_SESSION['user_id']
                    ]);

                    $updateStmt = $pdo->prepare('UPDATE productos SET stock = stock - :qty WHERE id = :id');
                    $updateStmt->execute([
                        ':qty' => $cantidad,
                        ':id' => $productoId
                    ]);

                    $pdo->commit();

                    $message = 'Venta registrada. Total con IVA: $' . number_format($total, 2, ',', '.');
                    $messageType = 'success';

                    $productos = $pdo->query('SELECT * FROM productos ORDER BY nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = 'Stock insuficiente para realizar la venta.';
                    $messageType = 'danger';
                }
            } else {
                $message = 'Producto no encontrado.';
                $messageType = 'danger';
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'Ocurrió un error al registrar la venta.';
            $messageType = 'danger';
        }
    } else {
        $message = 'Seleccione un producto y cantidad válida.';
        $messageType = 'danger';
    }
}

$ventas = [];
try {
    $ventasStmt = $pdo->query('SELECT v.id, v.qty, v.total, v.fecha, p.nombre FROM ventas v INNER JOIN productos p ON p.id = v.producto_id ORDER BY v.fecha DESC LIMIT 10');
    $ventas = $ventasStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ventas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Web - Ventas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div>
                <h1>Registrar Ventas</h1>
                <p>Realice ventas y controle el stock disponible.</p>
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
                <h2>Nueva venta</h2>
                <form method="post">
                    <input type="hidden" name="action" value="sell">
                    <div>
                        <label for="producto_id">Producto</label>
                        <select name="producto_id" id="producto_id" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?> - $<?php echo number_format($producto['precio'], 2, ',', '.'); ?> (Stock: <?php echo $producto['stock']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="cantidad">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-success" style="font-size:1.05rem;">Registrar venta</button>
                </form>
            </div>

            <div class="card">
                <h2>Últimas ventas</h2>
                <div class="list">
                    <?php foreach ($ventas as $venta): ?>
                        <div class="list-item">
                            <div>
                                <strong><?php echo htmlspecialchars($venta['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <div><?php echo $venta['qty']; ?> uds · <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></div>
                            </div>
                            <span class="badge badge-success">$<?php echo number_format($venta['total'], 2, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($ventas)): ?>
                        <p>No hay ventas registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
