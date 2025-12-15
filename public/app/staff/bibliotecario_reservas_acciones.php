<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

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

$estadoRaw = (string)($reserva['estado'] ?? '');
$estadoNorm = strtolower(trim($estadoRaw));
$hasFechaLimite = !empty($reserva['fecha_limite']);
if ($estadoNorm === '' && $hasFechaLimite) {
    $estadoNorm = 'en_curso';
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

    header('Location: ' . url_for('app/staff/bibliotecario_reservas.php'));
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

    header('Location: ' . url_for('app/staff/bibliotecario_reservas.php'));
    exit;
}

/* ============================================================
                 CANCELAR RESERVA
   ============================================================ */
if ($action === "cancelar") {

    // Si ya está cancelada/finalizada, no hacer nada
    if (in_array($estadoNorm, ['cancelado', 'finalizado'], true)) {
        header('Location: ' . url_for('app/staff/bibliotecario_reservas.php'));
        exit;
    }

    // Si estaba aprobada/en curso, devolver stock
    if (in_array($estadoNorm, ['en_curso', 'en curso', 'aprobado'], true)) {
        $db->prepare("UPDATE libros SET stock = stock + 1 WHERE id = ?")
           ->execute([$reserva['libro_id']]);
    }

    $db->prepare("UPDATE reservas SET estado = 'cancelado' WHERE id = ?")
       ->execute([$id]);

    header('Location: ' . url_for('app/staff/bibliotecario_reservas.php'));
    exit;
}

/* ============================================================
                 ELIMINAR RESERVA
   ============================================================ */
if ($action === "eliminar") {

    // Si está activa/aprobada, devolver stock antes de eliminar
    if (in_array($estadoNorm, ['en_curso', 'en curso', 'aprobado'], true)) {
        $db->prepare("UPDATE libros SET stock = stock + 1 WHERE id = ?")
           ->execute([$reserva['libro_id']]);
    }

    $db->prepare("DELETE FROM reservas WHERE id = ?")
       ->execute([$id]);

    header('Location: ' . url_for('app/staff/bibliotecario_reservas.php'));
    exit;
}

echo "Acción no válida";
