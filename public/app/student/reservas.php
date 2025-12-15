<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

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

    <section class="shelf">
        <h2 class="subtitle">Actualmente reservados</h2>

        <div class="books-row">

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
