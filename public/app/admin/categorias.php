<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador']);

$db = (new Database())->getConnection();

$mensaje = '';
$tipoMensaje = '';

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if ($id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM categorias_libros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $mensaje = "Categoría eliminada correctamente.";
            $tipoMensaje = "exito";
        } catch (Exception $e) {
            $mensaje = "No se pudo eliminar la categoría. Es posible que esté en uso.";
            $tipoMensaje = "error";
        }
    }
}

$categoriaEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, nombre FROM categorias_libros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $categoriaEditar = $stmt->fetch();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($nombre === '') {
        $mensaje = "El nombre de la categoría es obligatorio.";
        $tipoMensaje = "error";
    } else {
        try {
            if ($id > 0) {
                $sql = "UPDATE categorias_libros SET nombre = :nombre WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':nombre' => $nombre, ':id' => $id]);
                $mensaje = "Categoría actualizada correctamente.";
                $tipoMensaje = "exito";
            } else {
                $sql = "INSERT INTO categorias_libros (nombre) VALUES (:nombre)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':nombre' => $nombre]);
                $mensaje = "Categoría registrada correctamente.";
                $tipoMensaje = "exito";
            }
            $categoriaEditar = null;
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar la categoría.";
            $tipoMensaje = "error";
        }
    }
}

$stmt = $db->query("SELECT id, nombre FROM categorias_libros ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías | Biblioteca Digital</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>

<main class="content">
    <h1 class="title">Categorías de libros</h1>

    <?php if ($mensaje): ?>
        <p class="alert alert-<?php echo $tipoMensaje === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <section class="form-section">
        <h2><?php echo $categoriaEditar ? 'Editar categoría' : 'Nueva categoría'; ?></h2>

        <form method="post" action="" class="crud-form">
            <input type="hidden" name="id" value="<?php echo $categoriaEditar['id'] ?? 0; ?>">
            <label>
                Nombre de la categoría:
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($categoriaEditar['nombre'] ?? ''); ?>" required>
            </label>
            <div class="form-actions">
                <button type="submit"><?php echo $categoriaEditar ? 'Actualizar' : 'Guardar'; ?></button>
                <?php if ($categoriaEditar): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/admin/categorias.php')); ?>" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="list-section">
        <h2>Listado de categorías</h2>

        <?php if (count($categorias) === 0): ?>
            <p>No hay categorías registradas.</p>
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
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo $categoria['id']; ?></td>
                        <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(url_for('app/admin/categorias.php', ['editar' => $categoria['id']])); ?>">Editar</a>
                            |
                            <a href="<?php echo htmlspecialchars(url_for('app/admin/categorias.php', ['eliminar' => $categoria['id']])); ?>"
                               onclick="return confirm('¿Seguro que deseas eliminar esta categoría?');">
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
