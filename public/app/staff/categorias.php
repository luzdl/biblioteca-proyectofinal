<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'bibliotecario') {
    header("Location: ../public/login.php");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->getConnection();

$mensaje = "";

/* CREAR CATEGORÍA */
if (isset($_POST["nueva_categoria"])) {

    $nombre = trim($_POST["nueva_categoria"]);

    if ($nombre === "") {
        $mensaje = "El nombre de la categoría no puede estar vacío.";
    } else {
        /* Verificar duplicados */
        $check = $db->prepare("SELECT id FROM categorias_libros WHERE nombre = :n LIMIT 1");
        $check->execute([":n" => $nombre]);

        if ($check->fetch()) {
            $mensaje = "Ya existe una categoría con ese nombre.";
        } else {
            $insert = $db->prepare("INSERT INTO categorias_libros (nombre) VALUES (:n)");
            $insert->execute([":n" => $nombre]);
        }
    }
}

/* EDITAR CATEGORÍA */
if (isset($_POST["editar_id"])) {

    $id = intval($_POST["editar_id"]);
    $nombreNuevo = trim($_POST["editar_nombre"]);

    if ($nombreNuevo !== "") {
        $update = $db->prepare("UPDATE categorias_libros SET nombre = :n WHERE id = :id");
        $update->execute([
            ":n" => $nombreNuevo,
            ":id" => $id
        ]);
    }
}

/* ELIMINAR CATEGORÍA */
if (isset($_GET["eliminar"])) {

    $id = intval($_GET["eliminar"]);

    /* Verificar si tiene libros asociados */
    $check = $db->prepare("SELECT id FROM libros WHERE categoria_id = :id LIMIT 1");
    $check->execute([":id" => $id]);

    if ($check->fetch()) {
        $mensaje = "No puedes eliminar esta categoría porque tiene libros asociados.";
    } else {
        $del = $db->prepare("DELETE FROM categorias_libros WHERE id = :id");
        $del->execute([":id" => $id]);
    }
}

/* Obtener categorías actualizadas */
$categorias = $db->query("
    SELECT * FROM categorias_libros ORDER BY nombre ASC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Categorías de libros</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/bibliotecario.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body>

<?php 
$active = "categorias";
include "sidebar.php"; 
?>

<main class="content">

    <h1 class="page-title">Categorías de libros</h1>

    <?php if ($mensaje): ?>
        <p class="error-msg"><?= $mensaje ?></p>
    <?php endif; ?>

    <!-- FORM PARA CREAR CATEGORÍA -->
    <form method="POST" class="form-card small-card">
        <h3>Nueva categoría</h3>

        <div class="field">
            <label>Nombre</label>
            <input type="text" name="nueva_categoria" required>
        </div>

        <button type="submit" class="btn-save">Crear categoría</button>
    </form>

    <!-- LISTA DE CATEGORÍAS -->
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
                    
                    <!-- BOTÓN EDITAR (modal simple HTML) -->
                    <button class="btn-edit" onclick="mostrarEditar(<?= $cat['id'] ?>, '<?= $cat['nombre'] ?>')">
                        Editar
                    </button>

                    <!-- ELIMINAR -->
                    <a class="btn-delete"
                       href="categorias.php?eliminar=<?= $cat['id'] ?>"
                       onclick="return confirm('¿Eliminar esta categoría?')">
                        Eliminar
                    </a>

                </td>
            </tr>

            <?php endforeach; ?>
        </tbody>

    </table>

</main>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <h3>Editar categoría</h3>

        <form method="POST">
            <input type="hidden" name="editar_id" id="editar_id">

            <div class="field">
                <label>Nuevo nombre</label>
                <input type="text" name="editar_nombre" id="editar_nombre" required>
            </div>

            <button type="submit" class="btn-save">Guardar cambios</button>
            <button type="button" class="btn-close" onclick="cerrarModal()">Cancelar</button>
        </form>

    </div>
</div>

<script>
function mostrarEditar(id, nombre) {
    document.getElementById("editar_id").value = id;
    document.getElementById("editar_nombre").value = nombre;
    document.getElementById("modalEditar").style.display = "flex";
}

function cerrarModal() {
    document.getElementById("modalEditar").style.display = "none";
}
</script>

</body>
</html>
