<?php
require_once __DIR__ . '/auth.php';               // protege la página
require_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();

$mensaje = '';
$tipoMensaje = ''; // "error" o "exito"

// 1) ELIMINAR CARRERA
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

// 2) CARGAR CARRERA PARA EDICIÓN (si viene ?editar=ID)
$carreraEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, nombre FROM carreras WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $carreraEditar = $stmt->fetch();
    }
}

// 3) CREAR O ACTUALIZAR CARRERA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($nombre === '') {
        $mensaje = "El nombre de la carrera es obligatorio.";
        $tipoMensaje = "error";
    } else {
        try {
            if ($id > 0) {
                // actualizar
                $sql = "UPDATE carreras SET nombre = :nombre WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':id'     => $id,
                ]);
                $mensaje = "Carrera actualizada correctamente.";
                $tipoMensaje = "exito";
            } else {
                // crear
                $sql = "INSERT INTO carreras (nombre) VALUES (:nombre)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':nombre' => $nombre]);
                $mensaje = "Carrera registrada correctamente.";
                $tipoMensaje = "exito";
            }

            // limpiar datos del formulario de edición
            $carreraEditar = null;
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar la carrera.";
            $tipoMensaje = "error";
        }
    }
}

// 4) LISTAR TODAS LAS CARRERAS
$stmt = $db->query("SELECT id, nombre FROM carreras ORDER BY nombre");
$carreras = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carreras | Biblioteca Digital</title>
</head>
<body>
    <h1>Carreras</h1>

    <?php if ($mensaje): ?>
        <p style="padding:8px; border:1px solid #ccc; background:#f9f9f9; color:<?php echo $tipoMensaje === 'error' ? 'darkred' : 'darkgreen'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <!-- FORMULARIO: crear / editar -->
    <h2><?php echo $carreraEditar ? 'Editar carrera' : 'Nueva carrera'; ?></h2>

    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo $carreraEditar['id'] ?? 0; ?>">
        <label>
            Nombre de la carrera:
            <input
                type="text"
                name="nombre"
                value="<?php echo htmlspecialchars($carreraEditar['nombre'] ?? ''); ?>"
                required
            >
        </label>
        <button type="submit">
            <?php echo $carreraEditar ? 'Actualizar' : 'Guardar'; ?>
        </button>
        <?php if ($carreraEditar): ?>
            <a href="carreras.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <!-- LISTADO DE CARRERAS -->
    <h2>Listado de carreras</h2>

    <?php if (count($carreras) === 0): ?>
        <p>No hay carreras registradas.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
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
                        <a href="carreras.php?editar=<?php echo $carrera['id']; ?>">Editar</a>
                        |
                        <a href="carreras.php?eliminar=<?php echo $carrera['id']; ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar esta carrera?');">
                           Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="dashboard.php">Volver al inicio</a></p>
</body>
</html>
