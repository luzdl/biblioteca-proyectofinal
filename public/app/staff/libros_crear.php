<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'bibliotecario') {
    header("Location: ../public/login.php");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->getConnection();

/* Obtener categorías */
$categorias = $db->query("SELECT * FROM categorias_libros ORDER BY nombre ASC")->fetchAll();

$mensaje = "";

/* Procesar formulario */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = trim($_POST["titulo"]);
    $autor = trim($_POST["autor"]);
    $categoria_id = intval($_POST["categoria_id"]);
    $descripcion = trim($_POST["descripcion"]);
    $stock = intval($_POST["stock"]);

    /* Validación simple */
    if ($titulo === "" || $autor === "" || $categoria_id <= 0 || $stock < 0) {
        $mensaje = "Por favor completa todos los campos obligatorios.";
    } else {
        /* Subida de portada */
        $portadaNombre = null;

        if (!empty($_FILES["portada"]["name"])) {
            $archivo = $_FILES["portada"];
            $ext = pathinfo($archivo["name"], PATHINFO_EXTENSION);

            $portadaNombre = uniqid("libro_") . "." . $ext;

            move_uploaded_file(
                $archivo["tmp_name"],
                "../img/portadas/" . $portadaNombre
            );
        }

        /* Insertar libro */
        $sql = "INSERT INTO libros (titulo, autor, categoria_id, descripcion, portada, stock)
                VALUES (:titulo, :autor, :categoria_id, :descripcion, :portada, :stock)";
        
        $stmt = $db->prepare($sql);

        $stmt->execute([
            ":titulo" => $titulo,
            ":autor" => $autor,
            ":categoria_id" => $categoria_id,
            ":descripcion" => $descripcion,
            ":portada" => $portadaNombre,
            ":stock" => $stock
        ]);

        header("Location: libros.php?creado=1");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar libro</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/bibliotecario.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
</head>

<body>

<?php 
$active = "libros";
include "sidebar.php"; 
?>

<main class="content">

    <h1 class="page-title">Agregar nuevo libro</h1>

    <?php if ($mensaje): ?>
        <p class="error-msg"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-card">

        <div class="field">
            <label>Título *</label>
            <input type="text" name="titulo" required>
        </div>

        <div class="field">
            <label>Autor *</label>
            <input type="text" name="autor" required>
        </div>

        <div class="field">
            <label>Categoría *</label>
            <select name="categoria_id" required>
                <option value="">Seleccionar categoría</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Descripción</label>
            <textarea name="descripcion" rows="4"></textarea>
        </div>

        <div class="field">
            <label>Portada (opcional)</label>
            <input type="file" name="portada" accept="image/*">
        </div>

        <div class="field">
            <label>Stock *</label>
            <input type="number" name="stock" min="0" value="1" required>
        </div>

        <button type="submit" class="btn-save">Guardar libro</button>
    </form>

</main>

</body>
</html>
