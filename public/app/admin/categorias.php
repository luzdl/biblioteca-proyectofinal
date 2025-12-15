<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

$selfPath = current_role() === 'bibliotecario'
    ? 'app/staff/categorias.php'
    : 'app/admin/categorias.php';

$mensaje = "";
$tipoMensaje = "";

/* ==============================
   ELIMINAR CATEGORÍA
   ============================== */
if (isset($_GET['eliminar'])) {
    $id = Input::getInt('eliminar', 0);

    if ($id > 0) {
        try {
            $stmt = $db->prepare(
                "DELETE FROM categorias_libros WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            $mensaje = "Categoría eliminada correctamente.";
            $tipoMensaje = "success";
        } catch (Exception $e) {
            $mensaje = "No se pudo eliminar la categoría. Puede estar en uso.";
            $tipoMensaje = "error";
        }
    }
}

/* ==============================
   OBTENER CATEGORÍA A EDITAR
   ============================== */
$categoriaEditar = null;

if (isset($_GET['editar'])) {
    $id = Input::getInt('editar', 0);

    if ($id > 0) {
        $stmt = $db->prepare(
            "SELECT id, nombre FROM categorias_libros WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $categoriaEditar = $stmt->fetch();
    }
}

/* ==============================
   CREAR / ACTUALIZAR CATEGORÍA
   ============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = Input::postString('nombre');
    $id = Input::postInt('id', 0);

    $v = new Validator();
    $v->required('nombre', $nombre, 'El nombre de la categoría es obligatorio.');

    if (!$v->ok()) {
        $mensaje = $v->firstError();
        $tipoMensaje = "error";
    } else {
        try {
            if ($id > 0) {
                $stmt = $db->prepare(
                    "UPDATE categorias_libros SET nombre = :nombre WHERE id = :id"
                );
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':id' => $id
                ]);
                $mensaje = "Categoría actualizada correctamente.";
            } else {
                $stmt = $db->prepare(
                    "INSERT INTO categorias_libros (nombre) VALUES (:nombre)"
                );
                $stmt->execute([':nombre' => $nombre]);
                $mensaje = "Categoría creada correctamente.";
            }

            $tipoMensaje = "success";
            $categoriaEditar = null;

        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar la categoría.";
            $tipoMensaje = "error";
        }
    }
}

/* ==============================
   LISTAR CATEGORÍAS
   ============================== */
$categorias = $db->query(
    "SELECT id, nombre FROM categorias_libros ORDER BY nombre"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías de libros</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/bibliotecario.css')); ?>">
</head>

<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <h1 class="title">Categorías de libros</h1>

    <?php if ($mensaje): ?>
        <p class="alert <?= $tipoMensaje === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <section class="form-section">
        <h2><?php echo $categoriaEditar ? 'Editar categoría' : 'Nueva categoría'; ?></h2>

        <form method="post" action="" class="crud-form">
            <input type="hidden" name="id" value="<?= $categoriaEditar['id'] ?? 0 ?>">

            <label>
                Nombre:
                <input type="text" name="nombre" value="<?= htmlspecialchars($categoriaEditar['nombre'] ?? '') ?>" required>
            </label>

            <div class="form-actions">
                <button type="submit"><?php echo $categoriaEditar ? 'Actualizar' : 'Guardar'; ?></button>
                <?php if ($categoriaEditar): ?>
                    <a href="<?php echo htmlspecialchars(url_for($selfPath)); ?>" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- LISTADO -->
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
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(url_for($selfPath, ['editar' => $cat['id']])); ?>">Editar</a>
                            |
                            <a href="<?php echo htmlspecialchars(url_for($selfPath, ['eliminar' => $cat['id']])); ?>"
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
