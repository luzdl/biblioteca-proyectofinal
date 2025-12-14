<?php
session_start();
require_once __DIR__ . '/../config/router.php';

/* Verificar rol */
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login', ['next' => 'reservar.php?id=' . ($_GET['id'] ?? '')]);
}

 $id = $_GET["id"] ?? null;

if (!$id) {
    die("ID de libro no válido.");
}

// Aquí iría la lógica real de persistir la reserva en la base de datos.
// Para la versión simulada, redirigimos directamente a Mis reservas.
redirect('student_reservas');
