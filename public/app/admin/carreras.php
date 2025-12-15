<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

$mensaje = '';
$tipoMensaje = '';

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if ($id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM carreras WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $mensaje = "Carrera eliminada correctamente.";
            $tipoMensaje = "exito";
        } catch (Exception $e) {
            $mensaje = "No se pudo eliminar la carrera. Es posible que esté en uso.";
            $tipoMensaje = "error";
        }
    }
}

$carreraEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, nombre FROM carreras WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $carreraEditar = $stmt->fetch();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($nombre === '') {
        $mensaje = "El nombre de la carrera es obligatorio.";
        $tipoMensaje = "error";
    } else {
        try {
            if ($id > 0) {
                $sql = "UPDATE carreras SET nombre = :nombre WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':nombre' => $nombre, ':id' => $id]);
                $mensaje = "Carrera actualizada correctamente.";
                $tipoMensaje = "exito";
            } else {
                $sql = "INSERT INTO carreras (nombre) VALUES (:nombre)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':nombre' => $nombre]);
                $mensaje = "Carrera registrada correctamente.";
                $tipoMensaje = "exito";
            }
            $carreraEditar = null;
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar la carrera.";
            $tipoMensaje = "error";
        }
    }
}

$stmt = $db->query("SELECT id, nombre FROM carreras ORDER BY nombre");
$carreras = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carreras | Biblioteca Digital</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1 class="title">Carreras</h1>

    <?php if ($mensaje): ?>
        <p class="alert alert-<?php echo $tipoMensaje === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <section class="form-section">
        <h2><?php echo $carreraEditar ? 'Editar carrera' : 'Nueva carrera'; ?></h2>

        <form method="post" action="" class="crud-form">
            <input type="hidden" name="id" value="<?php echo $carreraEditar['id'] ?? 0; ?>">
            <label>
                Nombre de la carrera:
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($carreraEditar['nombre'] ?? ''); ?>" required>
            </label>
            <div class="form-actions">
                <button type="submit"><?php echo $carreraEditar ? 'Actualizar' : 'Guardar'; ?></button>
                <?php if ($carreraEditar): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="list-section">
        <h2>Listado de carreras</h2>

        <?php if (count($carreras) === 0): ?>
            <p>No hay carreras registradas.</p>
        <?php else: ?>
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($carreras as $carrera): ?>
                    <tr>
                        <td><?php echo $carrera['id']; ?></td>
                        <td><?php echo htmlspecialchars($carrera['nombre']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php', ['editar' => $carrera['id']])); ?>">Editar</a>
                            |
                            <a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php', ['eliminar' => $carrera['id']])); ?>"
                               onclick="return confirm('¿Seguro que deseas eliminar esta carrera?');">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
