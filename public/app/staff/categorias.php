<?php
require_once __DIR__ . '/../admin/categorias.php';
exit;

$db = (new Database())->getConnection();

$mensaje = "";

/* Eliminar categoría */
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if ($id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM categorias_libros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $mensaje = "Categoría eliminada correctamente.";
        } catch (Exception $e) {
            $mensaje = "No se pudo eliminar la categoría. Puede estar en uso.";
        }
    }
}

/* Editar categoría */
$categoriaEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT * FROM categorias_libros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $categoriaEditar = $stmt->fetch();
    }
}

/* Guardar categoría */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($nombre === '') {
        $mensaje = "El nombre es obligatorio.";
    } else {
        if ($id > 0) {
            $stmt = $db->prepare(
                "UPDATE categorias_libros SET nombre = :nombre WHERE id = :id"
            );
            $stmt->execute([':nombre' => $nombre, ':id' => $id]);
            $mensaje = "Categoría actualizada correctamente.";
        } else {
            $stmt = $db->prepare(
                "INSERT INTO categorias_libros (nombre) VALUES (:nombre)"
            );
            $stmt->execute([':nombre' => $nombre]);
            $mensaje = "Categoría creada correctamente.";
        }
        $categoriaEditar = null;
    }
}

/* Obtener categorías */
$categorias = $db->query(
    "SELECT * FROM categorias_libros ORDER BY nombre"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/bibliotecario.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <h1 class="page-title">Categorías de libros</h1>

    <?php if ($mensaje): ?>
        <p class="alert"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <h2><?= $categoriaEditar ? 'Editar categoría' : 'Nueva categoría' ?></h2>

    <form method="post" class="form-inline">
        <input type="hidden" name="id" value="<?= $categoriaEditar['id'] ?? 0 ?>">
        <input
            type="text"
            name="nombre"
            placeholder="Nombre"
            required
            value="<?= htmlspecialchars($categoriaEditar['nombre'] ?? '') ?>"
        >
        <button type="submit">
            <?= $categoriaEditar ? 'Guardar cambios' : 'Crear categoría' ?>
        </button>

        <?php if ($categoriaEditar): ?>
            <a href="categorias.php" class="btn-cancel">Cancelar</a>
        <?php endif; ?>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['nombre']) ?></td>
                    <td class="actions">
                        <a href="categorias.php?editar=<?= $cat['id'] ?>">Editar</a>
                        |
                        <a
                            href="categorias.php?eliminar=<?= $cat['id'] ?>"
                            onclick="return confirm('¿Eliminar esta categoría?')"
                        >
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>

</body>
</html>
