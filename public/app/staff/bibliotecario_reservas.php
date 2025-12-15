<?php
session_start();

// Restringir acceso solo a bibliotecarios
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'bibliotecario') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();

// Obtener reservas con datos del usuario y libro
$sql = "
    SELECT r.*, u.usuario AS nombre_usuario, l.titulo AS titulo_libro 
    FROM reservas r
    INNER JOIN usuarios u ON r.usuario_id = u.id
    INNER JOIN libros l ON r.libro_id = l.id
    ORDER BY r.fecha_reserva DESC
";

$reservas = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas pendientes | Bibliotecario</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        .content { margin-left: 260px; padding: 30px; }
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
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            color: white;
        }
        .aprobar { background: #6b8e23; }
        .finalizar { background: #7A5C3A; }
    </style>
</head>

<body>

<?php include 'sidebar_bibliotecario.php'; ?>

<div class="content">
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
            <td><?= ucfirst($r['estado']) ?></td>

            <td>
                <?php if ($r['estado'] === 'pendiente'): ?>
                    <a href="bibliotecario_reservas_acciones.php?action=aprobar&id=<?= $r['id'] ?>" class="btn aprobar">Aprobar</a>
                <?php endif ?>

                <?php if ($r['estado'] === 'en_curso'): ?>
                    <a href="bibliotecario_reservas_acciones.php?action=finalizar&id=<?= $r['id'] ?>" class="btn finalizar">Finalizar</a>
                <?php endif ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>
</div>

</body>
</html>
