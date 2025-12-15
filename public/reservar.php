<?php
session_start();
require_once __DIR__ . '/../config/router.php';
require_once __DIR__ . '/../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    $_SESSION['redirect_after_login'] = 'reservar.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    redirect('login');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    $_SESSION['mensaje'] = "ID de libro no válido";
    $_SESSION['tipo_mensaje'] = "error";
    redirect('home');
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Verificar si el libro existe y tiene stock
    $stmt = $db->prepare("SELECT id, titulo, stock FROM libros WHERE id = ? AND stock > 0");
    $stmt->execute([$id]);
    $libro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$libro) {
        throw new Exception("El libro no está disponible para reserva");
    }
    
    // Verificar si el usuario ya tiene una reserva activa para este libro
    $stmt = $db->prepare("SELECT id FROM reservas WHERE usuario_id = ? AND libro_id = ? AND estado IN ('pendiente', 'aprobado', 'en curso', 'en_curso') LIMIT 1");
    $stmt->execute([$_SESSION['usuario_id'], $id]);
    
    if ($stmt->fetch()) {
        throw new Exception("Ya tienes una reserva activa para este libro");
    }
    
    // Crear la reserva
    $stmt = $db->prepare("INSERT INTO reservas (usuario_id, libro_id, estado, fecha_reserva) VALUES (?, ?, 'pendiente', NOW())");
    
    if ($stmt->execute([$_SESSION['usuario_id'], $id])) {
        // Actualizar el stock del libro
        $updateStock = $db->prepare("UPDATE libros SET stock = stock - 1 WHERE id = ?");
        $updateStock->execute([$id]);
        
        $_SESSION['mensaje'] = "¡Libro reservado exitosamente!";
        $_SESSION['tipo_mensaje'] = "exito";
        redirect('student');
    } else {
        throw new Exception("Error al procesar la reserva");
    }
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
    redirect('home');
}
exit;
