<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

$db = (new Database())->getConnection();

$reservasAprobadas = [];
$reservasPendientes = [];

$todas = [];
try {
    $stmt = $db->prepare(
        "SELECT
            r.id,
            r.libro_id,
            r.estado,
            r.fecha_reserva,
            r.fecha_limite,
            l.titulo,
            l.autor,
            l.portada AS imagen
         FROM reservas r
         INNER JOIN libros l ON l.id = r.libro_id
         WHERE r.usuario_id = :usuario_id
           AND (
                r.estado IS NULL
                OR TRIM(r.estado) = ''
                OR LOWER(TRIM(r.estado)) <> 'cancelado'
           )
         ORDER BY r.fecha_reserva DESC"
    );
    $stmt->execute([':usuario_id' => (int)$_SESSION['usuario_id']]);
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log('Error cargando reservas del estudiante: ' . $e->getMessage());
    $todas = [];
}

// Deduplicar por libro_id y priorizar estado más avanzado
$porLibro = [];
foreach ($todas as $r) {
    $libroId = (int)($r['libro_id'] ?? 0);
    if ($libroId <= 0) {
        continue;
    }

    $estadoRaw = (string)($r['estado'] ?? '');
    $estadoNorm = strtolower(trim($estadoRaw));
    $hasFechaLimite = !empty($r['fecha_limite']);

    // Si el estado está vacío pero hay fecha límite, fue aprobada (BD no guardó el valor)
    if ($estadoNorm === '' && $hasFechaLimite) {
        $estadoNorm = 'en_curso';
    }

    if (in_array($estadoNorm, ['en_curso', 'en curso', 'aprobado', 'aprobada'], true)) {
        $rank = 3;
    } elseif ($estadoNorm === 'pendiente') {
        $rank = 2;
    } else {
        $rank = 1;
    }

    $current = $porLibro[$libroId] ?? null;
    if ($current === null || $rank > ($current['_rank'] ?? 0)) {
        $r['_rank'] = $rank;
        $porLibro[$libroId] = $r;
    }
}

foreach ($porLibro as $r) {
    $estadoRaw = (string)($r['estado'] ?? '');
    $estadoNorm = strtolower(trim($estadoRaw));
    $hasFechaLimite = !empty($r['fecha_limite']);
    if ($estadoNorm === '' && $hasFechaLimite) {
        $estadoNorm = 'en_curso';
    }

    if (in_array($estadoNorm, ['en_curso', 'en curso', 'aprobado', 'aprobada'], true)) {
        $reservasAprobadas[] = $r;
    } elseif ($estadoNorm === 'pendiente') {
        $reservasPendientes[] = $r;
    } else {
        $reservasPendientes[] = $r;
    }
}

$debug = isset($_GET['debug']) && $_GET['debug'] === '1';
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

    <?php if ($debug): ?>
        <pre style="background:#fff;padding:12px;border:1px solid #ddd;overflow:auto;max-width:100%;"><strong>DEBUG reservas (raw)</strong>
<?php foreach ($todas as $r): ?>
id=<?php echo (int)($r['id'] ?? 0); ?> libro_id=<?php echo (int)($r['libro_id'] ?? 0); ?> estado=<?php echo htmlspecialchars((string)($r['estado'] ?? '')); ?>
<?php endforeach; ?>
        </pre>
    <?php endif; ?>

    <section class="shelf">
        <h2 class="subtitle">Actualmente reservados</h2>

        <div class="books-row">

            <?php if (count($reservasAprobadas) === 0): ?>
                <p>No tienes reservas aprobadas aún.</p>
            <?php endif; ?>

            <?php foreach ($reservasAprobadas as $reserva): ?>
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

                    $estadoRaw = (string)($reserva['estado'] ?? '');
                    $estadoNorm = strtolower(trim($estadoRaw));
                    $hasFechaLimite = !empty($reserva['fecha_limite']);
                    if ($estadoNorm === '' && $hasFechaLimite) {
                        $estadoNorm = 'en_curso';
                    }
                    $estadoLabel = in_array($estadoNorm, ['en_curso', 'en curso', 'aprobado', 'aprobada'], true) ? 'Aprobado' : ($estadoRaw !== '' ? $estadoRaw : 'Aprobado');

                    $book = [
                        'imagen' => $imagenUrl,
                        'titulo' => $reserva['titulo'],
                        'autor'  => $reserva['autor'],
                    ];
                    $estadoClass = strtolower(str_replace(' ', '', $estadoLabel));
                    $extraHtml = '<p class="estado estado-' . $estadoClass . '">' . htmlspecialchars($estadoLabel) . '</p>';
                ?>
                <?php include __DIR__ . '/../../components/book_card.php'; ?>
            <?php endforeach; ?>

        </div>

        <div class="shelf-line"></div>
    </section>

    <section class="shelf">
        <h2 class="subtitle">Pendientes</h2>

        <div class="books-row">

            <?php if (count($reservasPendientes) === 0): ?>
                <p>No tienes reservas pendientes.</p>
            <?php endif; ?>

            <?php foreach ($reservasPendientes as $reserva): ?>
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

                    $estadoRaw = (string)($reserva['estado'] ?? '');
                    $estadoNorm = strtolower(trim($estadoRaw));
                    $hasFechaLimite = !empty($reserva['fecha_limite']);
                    if ($estadoNorm === '' && $hasFechaLimite) {
                        $estadoNorm = 'en_curso';
                    }
                    $estadoLabel = in_array($estadoNorm, ['en_curso', 'en curso', 'aprobado', 'aprobada'], true)
                        ? 'Aprobado'
                        : ($estadoRaw !== '' ? $estadoRaw : 'pendiente');

                    $book = [
                        'imagen' => $imagenUrl,
                        'titulo' => $reserva['titulo'],
                        'autor'  => $reserva['autor'],
                    ];
                    $estadoClass = strtolower(str_replace(' ', '', $estadoLabel));
                    $extraHtml = '<p class="estado estado-' . $estadoClass . '">' . htmlspecialchars($estadoLabel) . '</p>';

                    if (in_array($estadoNorm, ['pendiente', 'aprobado', 'aprobada'], true)) {
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
