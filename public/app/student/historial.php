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
         ORDER BY COALESCE(r.fecha_devolucion, r.fecha_reserva) DESC, r.id DESC"
    );
    $stmt->execute([':usuario_id' => (int)$_SESSION['usuario_id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_historial.css')); ?>">
    <style>
        .historial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
        }
        .historial-table th, .historial-table td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        .historial-table th {
            background-color: #f4f4f4;
        }
        .historial-table img {
            border-radius: 4px;
            width: 50px;
            height: auto;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <h1 class="title">Historial</h1>
    <h2 class="subtitle">Libros reservados anteriormente</h2>

    <?php if (count($historial) === 0): ?>
        <p>No tienes historial aún.</p>
    <?php else: ?>
        <table class="historial-table">
            <thead>
                <tr>
                    <th>Portada</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Estado</th>
                    <th>Fecha Reserva</th>
                    <th>Fecha Devolución</th>
                </tr>
            </thead>
            <tbody>
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

                        $estado = htmlspecialchars($h['estado'] ?? '');
                        $fechaReserva = !empty($h['fecha_reserva']) ? date('d/m/Y', strtotime($h['fecha_reserva'])) : '-';
                        $fechaDevolucion = !empty($h['fecha_devolucion']) ? date('d/m/Y', strtotime($h['fecha_devolucion'])) : '-';
                    ?>
                    <tr>
                        <td><img src="<?php echo $imagenUrl; ?>" alt="Portada"></td>
                        <td><?php echo htmlspecialchars($h['titulo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($h['autor'] ?? ''); ?></td>
                        <td><?php echo $estado; ?></td>
                        <td><?php echo $fechaReserva; ?></td>
                        <td><?php echo $fechaDevolucion; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</main>

</body>
</html>
