<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

$db = (new Database())->getConnection();

$historial = [];
try {
    $stmt = $db->prepare(
        "SELECT
            r.id,
            r.estado,
            r.fecha_reserva,
            r.fecha_devolucion,
            l.titulo,
            l.autor,
            l.portada AS imagen
         FROM reservas r
         INNER JOIN libros l ON l.id = r.libro_id
         WHERE r.usuario_id = :usuario_id
           AND (
                r.fecha_devolucion IS NOT NULL
                OR r.estado IN ('finalizado', 'cancelado')
           )
         ORDER BY COALESCE(r.fecha_devolucion, r.fecha_reserva) DESC, r.id DESC"
    );
    $stmt->execute([':usuario_id' => (int)$_SESSION['usuario_id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $historial = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_reservas.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <h1 class="title">Historial</h1>
    <h2 class="subtitle">Libros reservados anteriormente</h2>

    <div class="books-row">

        <?php if (count($historial) === 0): ?>
            <p>No tienes historial aún.</p>
        <?php endif; ?>

        <?php foreach ($historial as $h): ?>
            <?php
                $imagen = $h['imagen'] ?? '';
                if (is_string($imagen) && $imagen !== '') {
                    if (stripos($imagen, 'http://') === 0 || stripos($imagen, 'https://') === 0) {
                        $imagenUrl = $imagen;
                    } elseif (strpos($imagen, '/') !== false) {
                        $imagenUrl = url_for(ltrim($imagen, '/'));
                    } else {
                        $imagenUrl = url_for('img/portadas/' . ltrim($imagen, '/'));
                    }
                } else {
                    $imagenUrl = url_for('img/default-book.png');
                }

                $book = [
                    'imagen' => $imagenUrl,
                    'titulo' => $h['titulo'] ?? '',
                    'autor'  => $h['autor'] ?? '',
                ];

                $estado = (string)($h['estado'] ?? '');
                $estadoClass = strtolower(str_replace(' ', '', $estado));
                $extraHtml = '<p class="estado estado-' . $estadoClass . '">' . htmlspecialchars($estado) . '</p>';

                $fechaBase = $h['fecha_devolucion'] ?: ($h['fecha_reserva'] ?? null);
                $fechaTxt = '';
                if ($fechaBase) {
                    $fechaTxt = date('d/m/Y', strtotime((string)$fechaBase));
                }
                if ($fechaTxt !== '') {
                    $label = $h['fecha_devolucion'] ? 'Devuelto' : 'Reservado';
                    $extraHtml .= '<p class="estado">' . $label . ': ' . htmlspecialchars($fechaTxt) . '</p>';
                }
            ?>
            <?php include __DIR__ . '/../../components/book_card.php'; ?>
        <?php endforeach; ?>

    </div>

    <div class="shelf-line"></div>

</main>

</body>
</html>
