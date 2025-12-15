<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'bibliotecario') {
    header("Location: ../public/login.php");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->getConnection();

/* Obtiene lista de libros con su categoría */
$query = "
    SELECT libros.id, libros.titulo, libros.autor, libros.portada, libros.stock,
           categorias_libros.nombre AS categoria
    FROM libros
    INNER JOIN categorias_libros ON categorias_libros.id = libros.categoria_id
    ORDER BY libros.titulo ASC
";

$libros = $db->query($query)->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de libros</title>
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

    <div class="top-bar">
        <h1 class="page-title">Gestión de Libros</h1>
        <a href="libros_crear.php" class="btn-add">+ Añadir libro</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Portada</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($libros as $libro): ?>
                <tr>
                    <td>
                        <?php if ($libro['portada']): ?>
                            <img src="../img/portadas/<?= htmlspecialchars($libro['portada']); ?>" class="mini-portada">
                        <?php else: ?>
                            <span class="no-img">Sin imagen</span>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($libro['titulo']); ?></td>
                    <td><?= htmlspecialchars($libro['autor']); ?></td>
                    <td><?= htmlspecialchars($libro['categoria']); ?></td>

                    <td>
                        <?php if ($libro['stock'] > 0): ?>
                            <?= $libro['stock']; ?>
                        <?php else: ?>
                            <span class="agotado">Agotado</span>
                        <?php endif; ?>
                    </td>

                    <td class="actions">
                        <a class="btn-edit" href="libros_editar.php?id=<?= $libro['id'] ?>">Editar</a>
                        <a class="btn-delete" href="libros_eliminar.php?id=<?= $libro['id'] ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar este libro?')">
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
