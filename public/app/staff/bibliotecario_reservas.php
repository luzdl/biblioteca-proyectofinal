<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

// Obtener reservas con datos del usuario y libro
$sql = "
    SELECT r.*, u.usuario AS nombre_usuario, l.titulo AS titulo_libro 
    FROM reservas r
    INNER JOIN usuarios u ON r.usuario_id = u.id
    INNER JOIN libros l ON r.libro_id = l.id
    ORDER BY r.id DESC
";

$reservas = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas pendientes | Bibliotecario</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/bibliotecario.css')); ?>">
    <style>
        h1 { color: #7A5C3A; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e0d3c2;
        }
        th {
            background: #f3ebe1;
            color: #7A5C3A;
        }
        tr:hover { background: #f8f3ec; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            color: #4a3b28;
            font-weight: 600;
            border: 1px solid rgba(74, 59, 40, 0.18);
            box-shadow: 0 2px 6px rgba(0,0,0,0.10);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.14);
            filter: brightness(1.02);
        }
        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }
        .aprobar { background: #bbf7d0; }
        .finalizar { background: #e7d3c3; }
        .cancelar { background: #fecaca; }
        .eliminar { background: #fda4af; }
    </style>
</head>

<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1>Gestión de reservas</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Libro</th>
            <th>Fecha reserva</th>
            <th>Fecha límite</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($reservas as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['nombre_usuario']) ?></td>
            <td><?= htmlspecialchars($r['titulo_libro']) ?></td>
            <td><?= $r['fecha_reserva'] ?></td>
            <td><?= $r['fecha_limite'] ?? '---' ?></td>
            <td>
                <?php
                    $estadoRaw = (string)($r['estado'] ?? '');
                    $estadoRawTrim = trim($estadoRaw);
                    $hasFechaLimite = !empty($r['fecha_limite']);

                    if ($estadoRawTrim === '' && $hasFechaLimite) {
                        $estadoNorm = 'en_curso';
                    } elseif ($estadoRawTrim === '') {
                        $estadoNorm = 'pendiente';
                    } else {
                        $estadoNorm = strtolower(trim($estadoRawTrim));
                    }

                    echo htmlspecialchars(in_array($estadoNorm, ['aprobado', 'en_curso', 'en curso'], true) ? 'Aceptado' : ucfirst($estadoNorm));
                ?>
            </td>

            <td class="acciones">
                <?php if ($estadoNorm === 'pendiente'): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/staff/bibliotecario_reservas_acciones.php', ['action' => 'aprobar', 'id' => $r['id']])); ?>" class="btn aprobar" data-action="aprobar">Aprobar</a>
                    <a href="<?php echo htmlspecialchars(url_for('app/staff/bibliotecario_reservas_acciones.php', ['action' => 'cancelar', 'id' => $r['id']])); ?>" class="btn cancelar" data-action="cancelar">Cancelar</a>
                <?php endif ?>

                <?php if (in_array($estadoNorm, ['aprobado', 'en_curso', 'en curso'], true)): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/staff/bibliotecario_reservas_acciones.php', ['action' => 'finalizar', 'id' => $r['id']])); ?>" class="btn finalizar" data-action="finalizar">Finalizar</a>
                <?php endif ?>

                <?php if (in_array($estadoNorm, ['cancelado', 'finalizado'], true)): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/staff/bibliotecario_reservas_acciones.php', ['action' => 'eliminar', 'id' => $r['id']])); ?>" class="btn eliminar" data-action="eliminar">Eliminar</a>
                <?php endif ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a.btn[data-action]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();

            var href = this.getAttribute('href');
            var action = this.getAttribute('data-action') || '';

            var titles = {
                aprobar: '¿Aprobar reserva?',
                finalizar: '¿Finalizar reserva?',
                cancelar: '¿Cancelar reserva?',
                eliminar: '¿Eliminar reserva?'
            };

            var texts = {
                aprobar: '¿Está seguro que quiere aprobar esta reserva?\nSe descontará stock del libro.',
                finalizar: '¿Está seguro que quiere finalizar esta reserva?\nSe devolverá stock del libro.',
                cancelar: '¿Está seguro que desea cancelar esta reserva?',
                eliminar: '¿Está seguro que desea eliminar esta reserva?\nEsta acción no se puede deshacer.'
            };

            var icon = (action === 'eliminar') ? 'warning' : 'question';

            Swal.fire({
                title: titles[action] || 'Confirmación',
                text: texts[action] || '¿Está seguro?',
                icon: icon,
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
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
