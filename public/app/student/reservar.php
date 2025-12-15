<?php
session_start();

// Verificar que el usuario sea estudiante
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: ../../login.php");
    exit;
}

// Conexión a la base de datos
require_once __DIR__ . '/../../../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Obtener el ID del libro desde la URL
$libro_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$libro_id) {
    $_SESSION['error'] = "Libro no válido";
    header("Location: catalog.php");
    exit;
}

// Obtener el ID del usuario actual
$usuario_id = $_SESSION['usuario_id'];

try {
    // Verificar que el libro existe y tiene stock disponible
    $libro_query = "SELECT id, titulo, autor, stock FROM libros WHERE id = ?";
    $libro_stmt = $pdo->prepare($libro_query);
    $libro_stmt->execute([$libro_id]);
    $libro = $libro_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$libro) {
        $_SESSION['error'] = "El libro no existe";
        header("Location: catalog.php");
        exit;
    }
    
    if ($libro['stock'] <= 0) {
        $_SESSION['error'] = "El libro no está disponible en este momento";
        header("Location: catalog.php");
        exit;
    }
    
    // Verificar que el usuario no tenga ya una reserva activa de este libro
    $check_query = "SELECT id FROM reservas 
                    WHERE usuario_id = ? 
                    AND libro_id = ? 
                    AND estado IN ('activa', 'pendiente', 'en_curso')";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$usuario_id, $libro_id]);
    
    if ($check_stmt->fetch()) {
        $_SESSION['error'] = "Ya tienes una reserva activa de este libro";
        header("Location: catalog.php");
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Crear la reserva
    $fecha_limite = date('Y-m-d', strtotime('+14 days')); // 14 días para devolver
    
    $insert_query = "INSERT INTO reservas 
                     (usuario_id, libro_id, fecha_reserva, fecha_limite, estado) 
                     VALUES (?, ?, NOW(), ?, 'activa')";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->execute([$usuario_id, $libro_id, $fecha_limite]);
    
    // Reducir el stock del libro
    $update_stock = "UPDATE libros SET stock = stock - 1 WHERE id = ?";
    $stock_stmt = $pdo->prepare($update_stock);
    $stock_stmt->execute([$libro_id]);
    
    $pdo->commit();
    
    $_SESSION['success'] = "¡Reserva realizada con éxito! Tienes hasta el " . date('d/m/Y', strtotime($fecha_limite)) . " para devolverlo";
    header("Location: reservas.php");
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al realizar reserva: " . $e->getMessage());
    $_SESSION['error'] = "No se pudo realizar la reserva. Intenta de nuevo.";
    header("Location: catalog.php");
    exit;
}
?>