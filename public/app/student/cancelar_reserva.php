<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

// Verificar que se proporcionó un ID de reserva
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('ID de reserva no válido', 'error');
    redirect('student_reservas');
}

$reserva_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$db = (new Database())->getConnection();

try {
    // Verificar que la reserva pertenece al usuario actual
    $query = "SELECT id, estado FROM reservas WHERE id = :id AND usuario_id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $reserva_id,
        ':usuario_id' => $usuario_id
    ]);
    
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reserva) {
        set_flash_message('No se encontró la reserva o no tienes permiso para cancelarla', 'error');
        redirect('student_reservas');
    }
    
    // Solo permitir cancelar reservas pendientes o aprobadas
    if (!in_array($reserva['estado'], ['pendiente', 'aprobado'])) {
        set_flash_message('No se puede cancelar una reserva que ya está ' . $reserva['estado'], 'error');
        redirect('student_reservas');
    }
    
    // Actualizar el estado de la reserva a cancelada
    $updateQuery = "UPDATE reservas SET estado = 'cancelado' WHERE id = :id";
    $stmt = $db->prepare($updateQuery);
    $stmt->execute([':id' => $reserva_id]);
    
    set_flash_message('La reserva ha sido cancelada correctamente', 'success');
    
} catch (PDOException $e) {
    error_log('Error al cancelar la reserva: ' . $e->getMessage());
    set_flash_message('Ocurrió un error al intentar cancelar la reserva', 'error');
}

// Redirigir de vuelta a la página de reservas
redirect('student_reservas');
