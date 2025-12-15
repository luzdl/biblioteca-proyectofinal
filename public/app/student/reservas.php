<?php
session_start();

<<<<<<< HEAD
// Verificar que el usuario sea estudiante
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: ../../login.php");
    exit;
}

// Conexión a la base de datos
require_once __DIR__ . '/../../../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Obtener el ID del usuario actual
$usuario_id = $_SESSION['usuario_id'];

// Consultar reservas reales desde la base de datos
$query = "
    SELECT 
        r.id,
        r.libro_id,
        r.fecha_reserva,
        r.fecha_limite,
        r.estado,
        r.fecha_devolucion,
        l.titulo,
        l.autor,
        l.portada
    FROM reservas r
    INNER JOIN libros l ON r.libro_id = l.id
    WHERE r.usuario_id = ?
    AND r.estado IN ('activa', 'pendiente', 'en_curso')
    ORDER BY r.fecha_reserva DESC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario_id]);
    $reservas_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mapear los datos para mantener compatibilidad con tu vista
    $reservas = array_map(function($reserva) {
        // Mapear estados de la BD a nombres legibles
        $estados_map = [
            'activa' => 'En curso',
            'pendiente' => 'Pendiente',
            'en_curso' => 'En curso',
            'completada' => 'Finalizado',
            'cancelada' => 'Cancelada'
        ];
        
        return [
            'id' => $reserva['id'],
            'libro_id' => $reserva['libro_id'],
            'titulo' => $reserva['titulo'],
            'autor' => $reserva['autor'],
            'estado' => $estados_map[$reserva['estado']] ?? ucfirst($reserva['estado']),
            'fecha_reserva' => $reserva['fecha_reserva'],
            'fecha_limite' => $reserva['fecha_limite'],
            'imagen' => $reserva['portada'] 
                ? $reserva['portada']
                : 'img/libro_default.jpg',
        ];
    }, $reservas_db);
    
} catch (PDOException $e) {
    error_log("Error al obtener reservas: " . $e->getMessage());
    $reservas = [];
    $error_msg = "Error al cargar las reservas";
}

// Manejar cancelación de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_reserva'])) {
    $reserva_id = filter_input(INPUT_POST, 'reserva_id', FILTER_VALIDATE_INT);
    
    if ($reserva_id) {
        try {
            // Verificar que la reserva pertenece al usuario y está activa
            $check_query = "SELECT id, libro_id FROM reservas WHERE id = ? AND usuario_id = ? AND estado != 'cancelada'";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->execute([$reserva_id, $usuario_id]);
            
            $reserva_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reserva_data) {
                // Iniciar transacción para mantener consistencia
                $pdo->beginTransaction();
                
                // Actualizar estado de la reserva a cancelada
                $cancel_query = "UPDATE reservas SET estado = 'cancelada' WHERE id = ?";
                $cancel_stmt = $pdo->prepare($cancel_query);
                $cancel_stmt->execute([$reserva_id]);
                
                // Incrementar stock del libro
                $update_stock = "UPDATE libros SET stock = stock + 1 WHERE id = ?";
                $stock_stmt = $pdo->prepare($update_stock);
                $stock_stmt->execute([$reserva_data['libro_id']]);
                
                $pdo->commit();
                
                $_SESSION['success'] = "Reserva cancelada exitosamente";
                
                // Redirigir para evitar reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error_msg = "No tienes permiso para cancelar esta reserva";
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error al cancelar reserva: " . $e->getMessage());
            $error_msg = "No se pudo cancelar la reserva. Intenta de nuevo.";
        }
    }
}

// Función auxiliar para URLs (si no la tienes en otro lugar)
function url_for($path) {
    return '/biblioteca-proyectofinal/public/' . $path;
=======
$db = (new Database())->getConnection();

$reservas = [];
try {
    $stmt = $db->prepare(
        "SELECT
            r.id,
            r.estado,
            r.fecha_reserva,
            l.titulo,
            l.autor,
            l.portada AS imagen
         FROM reservas r
         INNER JOIN libros l ON l.id = r.libro_id
         WHERE r.usuario_id = :usuario_id
           AND r.estado <> 'cancelado'
         ORDER BY r.fecha_reserva DESC"
    );
    $stmt->execute([':usuario_id' => (int)$_SESSION['usuario_id']]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $reservas = [];
>>>>>>> 359ebd94ef92d7aad10cd8b9496c571ce938981b
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis reservas</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_reservas.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <h1 class="title">Mis reservas</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message" style="background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px; color: #155724; border: 1px solid #c3e6cb;">
            <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message" style="background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px; color: #721c24; border: 1px solid #f5c6cb;">
            <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="error-message" style="background: #fee; padding: 10px; margin: 10px 0; border-radius: 5px; color: #c00;">
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <section class="shelf">
        <h2 class="subtitle">Actualmente reservados</h2>

        <div class="books-row">

<<<<<<< HEAD
            <?php if (empty($reservas)): ?>
                <p class="no-reservas" style="padding: 20px; text-align: center; color: #666;">
                    No tienes reservas activas en este momento.
                </p>
            <?php else: ?>
                <?php foreach ($reservas as $reserva): ?>
                    <?php
                        $book = [
                            'imagen' => $reserva['imagen'],
                            'titulo' => $reserva['titulo'],
                            'autor'  => $reserva['autor'],
                        ];
                        $estadoClass = strtolower(str_replace(' ', '', $reserva['estado']));
                        $extraHtml = '<p class="estado estado-' . $estadoClass . '">' . htmlspecialchars($reserva['estado']) . '</p>';
                        
                        // Mostrar fecha límite si existe
                        if ($reserva['fecha_limite']) {
                            $extraHtml .= '<p class="fecha-limite" style="font-size: 0.9em; color: #666; margin: 5px 0;">Límite: ' . date('d/m/Y', strtotime($reserva['fecha_limite'])) . '</p>';
                        }
                        
                        $extraHtml .= '
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="reserva_id" value="' . $reserva['id'] . '">
                            <button type="submit" name="cancelar_reserva" class="cancel-btn" 
                                    onclick="return confirm(\'¿Estás seguro de cancelar esta reserva?\')">
                                Cancelar reserva
                            </button>
                        </form>';
                    ?>
                    <?php include __DIR__ . '/../../components/book_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
=======
            <?php if (count($reservas) === 0): ?>
                <p>No tienes reservas aún.</p>
            <?php endif; ?>

            <?php foreach ($reservas as $reserva): ?>
                <?php
                    $imagen = $reserva['imagen'] ?? '';
                    if (is_string($imagen) && $imagen !== '') {
                        if (stripos($imagen, 'http://') === 0 || stripos($imagen, 'https://') === 0) {
                            $imagenUrl = $imagen;
                        } else {
                            $imagenUrl = url_for(ltrim($imagen, '/'));
                        }
                    } else {
                        $imagenUrl = url_for('img/user_placeholder.png');
                    }

                    $book = [
                        'imagen' => $imagenUrl,
                        'titulo' => $reserva['titulo'],
                        'autor'  => $reserva['autor'],
                    ];
                    $estadoClass = strtolower(str_replace(' ', '', $reserva['estado']));
                    $extraHtml = '<p class="estado estado-' . $estadoClass . '">' . htmlspecialchars($reserva['estado']) . '</p>';

                    if (in_array($reserva['estado'], ['pendiente', 'aprobado', 'en curso'], true)) {
                        $extraHtml .= '<a href="cancelar_reserva.php?id=' . intval($reserva['id']) . '" class="cancel-btn">Cancelar reserva</a>';
                    }
                ?>
                <?php include __DIR__ . '/../../components/book_card.php'; ?>
            <?php endforeach; ?>
>>>>>>> 359ebd94ef92d7aad10cd8b9496c571ce938981b

        </div>

        <div class="shelf-line"></div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a.cancel-btn').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            if (!href) {
                return;
            }

            Swal.fire({
                title: '¿Está seguro de cancelar su reserva?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});
</script>

</body>
</html>