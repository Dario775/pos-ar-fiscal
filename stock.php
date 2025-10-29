<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add') {
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';

        if ($nombre !== '' && is_numeric($precio) && is_numeric($stock)) {
            try {
                $stmt = $pdo->prepare('INSERT INTO productos (nombre, precio, stock) VALUES (:nombre, :precio, :stock)');
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':precio' => $precio,
                    ':stock' => $stock
                ]);
                $message = 'Producto agregado correctamente.';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'No se pudo agregar el producto.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Complete todos los campos del producto.';
            $messageType = 'danger';
        }
    }

    if (($_POST['action'] ?? '') === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['edit_nombre'] ?? '');
        $precio = $_POST['edit_precio'] ?? '';
        $stock = $_POST['edit_stock'] ?? '';

        if ($id && $nombre !== '' && is_numeric($precio) && is_numeric($stock)) {
            try {
                $stmt = $pdo->prepare('UPDATE productos SET nombre = :nombre, precio = :precio, stock = :stock WHERE id = :id');
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':precio' => $precio,
                    ':stock' => $stock,
                    ':id' => $id
                ]);
                $message = 'Producto actualizado correctamente.';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'No se pudo actualizar el producto.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Complete todos los campos para actualizar el producto.';
            $messageType = 'danger';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id) {
        try {
            $stmt = $pdo->prepare('DELETE FROM productos WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = 'Producto eliminado.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'No se pudo eliminar el producto.';
            $messageType = 'danger';
        }
    }
}

$productos = [];
try {
    $productos = $pdo->query('SELECT * FROM productos ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Web - Stock</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete(event, url) {
            event.preventDefault();
            if (confirm('¿Está seguro de eliminar este producto?')) {
                window.location.href = url;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <header class="header">
            <div>
                <h1>Gestión de Stock</h1>
                <p>Administre el inventario de productos.</p>
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
                <h2>Agregar producto</h2>
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div>
                        <label for="precio">Precio</label>
                        <input type="number" step="0.01" id="precio" name="precio" required>
                    </div>
                    <div>
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" required>
                    </div>
                    <button type="submit" class="btn btn-success">Agregar</button>
                </form>
            </div>

            <div class="card">
                <h2>Productos</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td>
                                        <div class="actions">
                                            <form method="post">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                                <input type="text" name="edit_nombre" value="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                <input type="number" step="0.01" name="edit_precio" value="<?php echo $producto['precio']; ?>" required>
                                                <input type="number" name="edit_stock" value="<?php echo $producto['stock']; ?>" required>
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                            </form>
                                            <a class="btn btn-danger" href="?delete=<?php echo $producto['id']; ?>" onclick="confirmDelete(event, this.href)">Eliminar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="5">No hay productos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
