<?php
session_start();

require_once __DIR__ . '/../config/router.php';
require_once __DIR__ . '/../config/database.php';

/* Verificar rol */
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login');
}

$reservaId = $_GET['id'] ?? null;

if (!$reservaId) {
    die("Reserva inválida.");
}

$db = (new Database())->getConnection();

/* 1️⃣ Verificar que la reserva exista y pertenezca al usuario */
$sql = "SELECT r.id, r.libro_id
        FROM reservas r
        WHERE r.id = :id
          AND r.usuario_id = :usuario
          AND r.estado IN ('pendiente', 'en curso')
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':id' => $reservaId,
    ':usuario' => $_SESSION['usuario_id']
]);

$reserva = $stmt->fetch();

if (!$reserva) {
    die("No puedes cancelar esta reserva.");
}

/* 2️⃣ Cancelar la reserva */
$sql = "UPDATE reservas SET estado = 'cancelada' WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $reservaId]);

/* 3️⃣ Devolver el libro al stock */
$sql = "UPDATE libros
        SET stock = stock + 1,
            disponible = 1
        WHERE id = :libro";

$stmt = $db->prepare($sql);
$stmt->execute([':libro' => $reserva['libro_id']]);

/* 4️⃣ Redirigir */
redirect('student_reservas');
