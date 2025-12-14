<?php
session_start();

require_once __DIR__ . '/../config/router.php';
require_once __DIR__ . '/../config/database.php';

/* Validar sesión */
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login');
}

$usuarioId = $_SESSION['usuario_id'];
$libroId   = $_GET['id'] ?? null;

if (!$libroId || !is_numeric($libroId)) {
    die("Libro no válido.");
}

/* Conexión */
$db  = new Database();
$pdo = $db->getConnection();

/* 1️⃣ Verificar libro */
$stmt = $pdo->prepare("
    SELECT id, stock 
    FROM libros 
    WHERE id = ?
");
$stmt->execute([$libroId]);
$libro = $stmt->fetch();

if (!$libro) {
    die("El libro no existe.");
}

if ($libro['stock'] <= 0) {
    die("No hay stock disponible.");
}

/* 2️⃣ Verificar reserva activa */
$stmt = $pdo->prepare("
    SELECT id
    FROM reservas
    WHERE usuario_id = ?
      AND libro_id = ?
      AND estado IN ('pendiente', 'en curso')
");
$stmt->execute([$usuarioId, $libroId]);

if ($stmt->fetch()) {
    die("Ya tienes una reserva activa de este libro.");
}

/* 3️⃣ Crear reserva */
$stmt = $pdo->prepare("
    INSERT INTO reservas (usuario_id, libro_id, estado, fecha_reserva)
    VALUES (?, ?, 'pendiente', NOW())
");
$stmt->execute([$usuarioId, $libroId]);

/* 4️⃣ Actualizar stock */
$stmt = $pdo->prepare("
    UPDATE libros
    SET stock = stock - 1
    WHERE id = ?
");
$stmt->execute([$libroId]);

/* 5️⃣ Redirigir */
redirect('student_reservas');
