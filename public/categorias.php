<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/admin_only.php';       
require_once __DIR__ . '/../config/database.php';


$db = (new Database())->getConnection();

$mensaje = '';
$tipoMensaje = ''; // "error" o "exito"

// 1) ELIMINAR CATEGORÍA
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

// 2) CARGAR CATEGORÍA PARA EDICIÓN (si viene ?editar=ID)
$categoriaEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, nombre FROM categorias_libros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $categoriaEditar = $stmt->fetch();
    }
}

// 3) CREAR O ACTUALIZAR CATEGORÍA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($nombre === '') {
        $mensaje = "El nombre de la categoría es obligatorio.";
        $tipoMensaje = "error";
    } else {
        try {
            if ($id > 0) {
                // actualizar
                $sql = "UPDATE categorias_libros SET nombre = :nombre WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':id'     => $id,
                ]);
                $mensaje = "Categoría actualizada correctamente.";
                $tipoMensaje = "exito";
            } else {
                // crear
                $sql = "INSERT INTO categorias_libros (nombre) VALUES (:nombre)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':nombre' => $nombre]);
                $mensaje = "Categoría registrada correctamente.";
                $tipoMensaje = "exito";
            }

            // limpiar datos del formulario de edición
            $categoriaEditar = null;
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar la categoría.";
            $tipoMensaje = "error";
        }
    }
}

// 4) LISTAR TODAS LAS CATEGORÍAS
$stmt = $db->query("SELECT id, nombre FROM categorias_libros ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías de libros | Biblioteca Digital</title>
</head>
<body>
    <h1>Categorías de libros</h1>

    <?php if ($mensaje): ?>
        <p style="padding:8px; border:1px solid #ccc; background:#f9f9f9; color:<?php echo $tipoMensaje === 'error' ? 'darkred' : 'darkgreen'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <!-- FORMULARIO: crear / editar -->
    <h2><?php echo $categoriaEditar ? 'Editar categoría' : 'Nueva categoría'; ?></h2>

    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo $categoriaEditar['id'] ?? 0; ?>">
        <label>
            Nombre de la categoría:
            <input
                type="text"
                name="nombre"
                value="<?php echo htmlspecialchars($categoriaEditar['nombre'] ?? ''); ?>"
                required
            >
        </label>
        <button type="submit">
            <?php echo $categoriaEditar ? 'Actualizar' : 'Guardar'; ?>
        </button>
        <?php if ($categoriaEditar): ?>
            <a href="categorias.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <!-- LISTADO DE CATEGORÍAS -->
    <h2>Listado de categorías</h2>

    <?php if (count($categorias) === 0): ?>
        <p>No hay categorías registradas.</p>
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
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?php echo $categoria['id']; ?></td>
                    <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                    <td>
                        <a href="categorias.php?editar=<?php echo $categoria['id']; ?>">Editar</a>
                        |
                        <a href="categorias.php?eliminar=<?php echo $categoria['id']; ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar esta categoría?');">
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
