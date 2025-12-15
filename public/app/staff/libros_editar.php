<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'bibliotecario') {
    header("Location: ../public/login.php");
    exit;
}

require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../config/env.php";

$db = (new Database())->getConnection();

/* Obtener ID del libro */
if (!isset($_GET["id"])) {
    header("Location: libros.php");
    exit;
}

$id = intval($_GET["id"]);

/* Cargar datos del libro */
$stmt = $db->prepare("
    SELECT * FROM libros WHERE id = :id LIMIT 1
");
$stmt->execute([":id" => $id]);
$libro = $stmt->fetch();

if (!$libro) {
    die("Libro no encontrado.");
}

/* Cargar categorías */
$categorias = $db->query("SELECT * FROM categorias_libros ORDER BY nombre ASC")->fetchAll();

$mensaje = "";

/* Procesar formulario */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = trim($_POST["titulo"]);
    $autor = trim($_POST["autor"]);
    $categoria_id = intval($_POST["categoria_id"]);
    $descripcion = trim($_POST["descripcion"]);
    $stock = intval($_POST["stock"]);

    if ($titulo === "" || $autor === "" || $categoria_id <= 0 || $stock < 0) {
        $mensaje = "Completa todos los campos obligatorios.";
    } else {

        $portadaNueva = $libro["portada"]; // Mantener la actual

        /* ¿Subieron una nueva portada? */
        if (!empty($_FILES["portada"]["name"])) {

            $archivo = $_FILES["portada"];
            $ext = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));

            $nombreNuevo = uniqid("libro_") . "." . $ext;

            move_uploaded_file($archivo["tmp_name"], "../img/portadas/" . $nombreNuevo);

            $portadaNueva = $nombreNuevo;
        }

        /* Actualizar libro */
        $sql = "UPDATE libros 
                SET titulo = :titulo,
                    autor = :autor,
                    categoria_id = :categoria_id,
                    descripcion = :descripcion,
                    portada = :portada,
                    stock = :stock
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ":titulo" => $titulo,
            ":autor" => $autor,
            ":categoria_id" => $categoria_id,
            ":descripcion" => $descripcion,
            ":portada" => $portadaNueva,
            ":stock" => $stock,
            ":id" => $id
        ]);

        header("Location: libros.php?editado=1");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar libro</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/bibliotecario.css">
</head>

<body>

<?php 
$active = "libros";
include "sidebar.php"; 
?>

<main class="content">

    <h1 class="page-title">Editar libro</h1>

    <?php if ($mensaje): ?>
        <p class="error-msg"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-card">

        <div class="field">
            <label>Título *</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" required>
        </div>

        <div class="field">
            <label>Autor *</label>
            <input type="text" name="autor" value="<?= htmlspecialchars($libro['autor']) ?>" required>
        </div>

        <div class="field">
            <label>Categoría *</label>
            <select name="categoria_id" required>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>" 
                        <?= $c['id'] == $libro['categoria_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Descripción</label>
            <textarea name="descripcion" rows="4"><?= htmlspecialchars($libro['descripcion']) ?></textarea>
        </div>

        <div class="field">
            <label>Portada actual</label>
            <?php if ($libro['portada']): ?>
                <img src="../img/portadas/<?= htmlspecialchars($libro['portada']) ?>" class="edit-portada">
            <?php else: ?>
                <p class="no-img">Sin portada</p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label>Nueva portada (opcional)</label>
            <input type="file" name="portada" accept="image/*">
        </div>

        <div class="field">
            <label>Stock *</label>
            <input type="number" name="stock" min="0" value="<?= $libro['stock'] ?>" required>
        </div>

        <button type="submit" class="btn-save">Guardar cambios</button>

    </form>

</main>

</body>
</html>
