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

$mensaje = "";
$tipoMensaje = "";

/* ==============================
   ELIMINAR CATEGORÍA
   ============================== */
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];

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
    $id = (int) $_GET['editar'];

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
    $nombre = trim($_POST['nombre'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);

    if ($nombre === '') {
        $mensaje = "El nombre de la categoría es obligatorio.";
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

    <!-- ESTILOS CORRECTOS -->
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../../../css/bibliotecario.css">

    <!-- ICONOS -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body>

<?php
    $active = "categorias";
    include __DIR__ . "/sidebar.php";
?>

<main class="content">

    <h1 class="page-title">Categorías de libros</h1>

    <?php if ($mensaje): ?>
        <p class="alert <?= $tipoMensaje === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <section class="form-card small-card">
        <h3><?= $categoriaEditar ? 'Editar categoría' : 'Nueva categoría' ?></h3>

        <form method="post">
            <input type="hidden" name="id" value="<?= $categoriaEditar['id'] ?? 0 ?>">

            <div class="field">
                <label>Nombre</label>
                <input
                    type="text"
                    name="nombre"
                    required
                    value="<?= htmlspecialchars($categoriaEditar['nombre'] ?? '') ?>"
                >
            </div>

            <button type="submit" class="btn-save">
                <?= $categoriaEditar ? 'Guardar cambios' : 'Crear categoría' ?>
            </button>

            <?php if ($categoriaEditar): ?>
                <a href="categorias.php" class="btn-close">Cancelar</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- LISTADO -->
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th width="200">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categorias as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                <td class="actions">
                    <a class="btn-edit" href="categorias.php?editar=<?= $cat['id'] ?>">
                        Editar
                    </a>
                    <a
                        class="btn-delete"
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
