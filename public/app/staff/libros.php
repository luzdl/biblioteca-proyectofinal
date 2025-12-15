<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

/* ==============================
   OBTENER LIBROS CON CATEGORÍA
   ============================== */
$query = "
    SELECT 
        libros.id,
        libros.titulo,
        libros.autor,
        libros.portada,
        libros.stock,
        categorias_libros.nombre AS categoria
    FROM libros
    INNER JOIN categorias_libros 
        ON categorias_libros.id = libros.categoria_id
    ORDER BY libros.titulo ASC
";

$libros = $db->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de libros</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/bibliotecario.css')); ?>">
</head>

<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <div class="top-bar">
        <h1 class="page-title">Gestión de Libros</h1>
        <a href="<?php echo htmlspecialchars(url_for('app/staff/libros_crear.php')); ?>" class="btn-add">+ Añadir libro</a>
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
                            <img 
                                src="<?php echo htmlspecialchars(url_for('img/portadas/' . $libro['portada'])); ?>" 
                                class="mini-portada"
                                alt="Portada"
                            >
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
                        <a class="btn-edit" href="<?php echo htmlspecialchars(url_for('app/staff/libros_editar.php', ['id' => $libro['id']])); ?>">Editar</a>
                        <a 
                            class="btn-delete"
                            href="<?php echo htmlspecialchars(url_for('app/staff/libros_eliminar.php', ['id' => $libro['id']])); ?>"
                            onclick="return confirm('¿Seguro que deseas eliminar este libro?')"
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
