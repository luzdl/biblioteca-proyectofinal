<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();

$action = $_GET['action'] ?? null;
$id = intval($_GET['id'] ?? 0);

if (!$action || !$id) {
    die("Solicitud inválida");
}

// Obtener reserva
$sql = "SELECT * FROM reservas WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id]);
$reserva = $stmt->fetch();

if (!$reserva) {
    die("Reserva no encontrada");
}

/* ============================================================
                APROBAR RESERVA
   ============================================================ */
if ($action === "aprobar") {

    // fecha límite = hoy + 7 días
    $fecha_limite = date("Y-m-d", strtotime("+7 days"));

    $db->prepare("
        UPDATE reservas 
        SET estado = 'en_curso', fecha_limite = ?
        WHERE id = ?
    ")->execute([$fecha_limite, $id]);

    // reducir stock del libro
    $db->prepare("UPDATE libros SET stock = stock - 1 WHERE id = ?")
       ->execute([$reserva['libro_id']]);

    header("Location: bibliotecario_reservas.php");
    exit;
}

/* ============================================================
                 FINALIZAR RESERVA
   ============================================================ */
if ($action === "finalizar") {

    $fecha_devolucion = date("Y-m-d");

    $db->prepare("
        UPDATE reservas
        SET estado = 'finalizado', fecha_devolucion = ?
        WHERE id = ?
    ")->execute([$fecha_devolucion, $id]);

    // devolver stock
    $db->prepare("UPDATE libros SET stock = stock + 1 WHERE id = ?")
       ->execute([$reserva['libro_id']]);

    header("Location: bibliotecario_reservas.php");
    exit;
}

echo "Acción no válida";
