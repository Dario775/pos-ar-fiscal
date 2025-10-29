<?php
session_start();

$dsn = 'sqlite:' . __DIR__ . '/pos.db';

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec('CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS productos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        precio REAL NOT NULL,
        stock INTEGER NOT NULL DEFAULT 0
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS ventas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        producto_id INTEGER NOT NULL,
        qty INTEGER NOT NULL,
        total REAL NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_id INTEGER NOT NULL,
        FOREIGN KEY(producto_id) REFERENCES productos(id),
        FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
    )');

    $stmt = $pdo->query('SELECT COUNT(*) FROM usuarios');
    if ($stmt->fetchColumn() == 0) {
        $insertUser = $pdo->prepare('INSERT INTO usuarios (username, password) VALUES (:username, :password)');
        $insertUser->execute([
            ':username' => 'admin',
            ':password' => password_hash('admin', PASSWORD_DEFAULT)
        ]);
    }

    $stmt = $pdo->query('SELECT COUNT(*) FROM productos');
    if ($stmt->fetchColumn() == 0) {
        $insertProduct = $pdo->prepare('INSERT INTO productos (nombre, precio, stock) VALUES (:nombre, :precio, :stock)');
        $insertProduct->execute([
            ':nombre' => 'Coca Cola 2L',
            ':precio' => 850,
            ':stock' => 50
        ]);
        $insertProduct->execute([
            ':nombre' => 'Alfajor Jorgito',
            ':precio' => 120,
            ':stock' => 100
        ]);
    }
} catch (PDOException $e) {
    die('Error al conectar con la base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
