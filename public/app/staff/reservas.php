<?php
session_start();

/* ==============================
   VALIDAR ACCESO DEL BIBLIOTECARIO
   ============================== */
if (
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'bibliotecario'
) {
    header("Location: ../../login.php");
    exit;
}

/* ==============================
   CONEXIÓN A LA BASE DE DATOS
   ============================== */
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../config/env.php";

$db = (new Database())->getConnection();

/* ==============================
   PROCESAR ACCIONES (POST)
   ============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reserva_id = (int) ($_POST['reserva_id'] ?? 0);
    $accion     = $_POST['accion'] ?? '';

    if ($reserva_id > 0) {

        if ($accion === 'aprobar') {
            // pendiente → en_curso
            $stmt = $db->prepare(
                "UPDATE reservas SET estado = 'en_curso' WHERE id = :id"
            );
            $stmt->execute([':id' => $reserva_id]);

        } elseif ($accion === 'finalizar') {
            // en curso → finalizado
            $stmt = $db->prepare(
                "UPDATE reservas 
                 SET estado = 'finalizado', fecha_devolucion = CURDATE()
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $reserva_id]);

        } elseif ($accion === 'cancelar') {
            // cancelar reserva
            $stmt = $db->prepare(
                "UPDATE reservas SET estado = 'cancelado' WHERE id = :id"
            );
            $stmt->execute([':id' => $reserva_id]);
        }
    }

    header("Location: reservas.php");
    exit;
}

/* ==============================
   OBTENER RESERVAS
   ============================== */
$reservas = $db->query(
    "SELECT
        r.id,
        r.estado,
        r.fecha_reserva,
        r.fecha_limite,
        r.fecha_devolucion,
        u.usuario AS usuario,
        l.titulo AS libro
     FROM reservas r
     INNER JOIN usuarios u ON u.id = r.usuario_id
     INNER JOIN libros l ON l.id = r.libro_id
     ORDER BY r.fecha_reserva DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas</title>

    <!-- ESTILOS -->
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../../../css/bibliotecario.css">

    <!-- ICONOS -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>

<?php
$active = "reservas";
include __DIR__ . "/sidebar.php";
?>

<main class="content">

    <h1 class="page-title">Reservas</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Libro</th>
                <th>Fecha reserva</th>
                <th>Estado</th>
                <th width="260">Acciones</th>
            </tr>
        </thead>
        <tbody>

        <?php if (count($reservas) === 0): ?>
            <tr>
                <td colspan="5">No hay reservas registradas.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($reservas as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['usuario']) ?></td>
                <td><?= htmlspecialchars($r['libro']) ?></td>
                <td><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></td>
                <td>
                    <?php
                        $estadoRaw = (string)($r['estado'] ?? '');
                        $estadoRawTrim = trim($estadoRaw);
                        $hasFechaLimite = !empty($r['fecha_limite']);

                        // Si el estado está vacío pero hay fecha límite, fue aprobada (DB no guardó el valor)
                        if ($estadoRawTrim === '' && $hasFechaLimite) {
                            $estadoNorm = 'en_curso';
                        } elseif ($estadoRawTrim === '') {
                            $estadoNorm = 'pendiente';
                        } else {
                            $estadoNorm = strtolower(trim($estadoRawTrim));
                        }

                        $estadoClass = str_replace(' ', '-', $estadoNorm);
                        $estadoLabel = in_array($estadoNorm, ['aprobado', 'en_curso', 'en curso'], true) ? 'Aceptado' : ucfirst($estadoNorm);
                    ?>
                    <span class="estado <?= htmlspecialchars($estadoClass) ?>">
                        <?= htmlspecialchars($estadoLabel) ?>
                    </span>
                </td>
                <td class="actions">

                    <?php if ($estadoNorm === 'pendiente'): ?>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="accion" value="aprobar">
                            <button class="btn-approve">Aprobar</button>
                        </form>

                        <form method="post" style="display:inline">
                            <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="accion" value="cancelar">
                            <button class="btn-delete">Cancelar</button>
                        </form>

                    <?php elseif (in_array($estadoNorm, ['aprobado', 'en curso', 'en_curso'], true)): ?>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="accion" value="finalizar">
                            <button class="btn-finish">Finalizar</button>
                        </form>

                    <?php else: ?>
                        —
                    <?php endif; ?>

                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

</main>

</body>
</html>
