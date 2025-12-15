<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

require_once __DIR__ . '/../../components/libros_export.php';
if (function_exists('libros_export_handle_export')) {
    libros_export_handle_export($db, ['page_path' => 'app/staff/libros.php']);
}

$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $idEliminar = (int)($_POST['id'] ?? 0);

    if ($idEliminar <= 0) {
        $mensaje = 'Solicitud inválida.';
        $tipoMensaje = 'error';
    } else {
        try {
            $check = $db->prepare('SELECT COUNT(*) FROM reservas WHERE libro_id = :id');
            $check->execute([':id' => $idEliminar]);
            $enUso = (int)$check->fetchColumn();

            if ($enUso > 0) {
                header('Location: ' . url_for('app/staff/libros.php', ['error' => 'en_uso']));
                exit;
            }

            $del = $db->prepare('DELETE FROM libros WHERE id = :id');
            $del->execute([':id' => $idEliminar]);

            header('Location: ' . url_for('app/staff/libros.php', ['eliminado' => 1]));
            exit;
        } catch (Exception $e) {
            header('Location: ' . url_for('app/staff/libros.php', ['error' => 1]));
            exit;
        }
    }
}

if (isset($_GET['eliminado'])) {
    $mensaje = 'Libro eliminado correctamente.';
    $tipoMensaje = 'success';
} elseif (isset($_GET['error']) && $_GET['error'] === 'en_uso') {
    $mensaje = 'No se puede eliminar el libro porque tiene reservas asociadas.';
    $tipoMensaje = 'error';
} elseif (isset($_GET['error'])) {
    $mensaje = 'Ocurrió un error al eliminar el libro.';
    $tipoMensaje = 'error';
}

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
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="<?php echo htmlspecialchars(url_for('app/staff/libros.php', ['export_libros' => 1, 'format' => 'xls'])); ?>" class="btn-add">Exportar (Excel)</a>
            <a href="<?php echo htmlspecialchars(url_for('app/staff/libros_crear.php')); ?>" class="btn-add">+ Añadir libro</a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <p class="alert <?php echo $tipoMensaje === 'error' ? 'alert-error' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

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
                        <form method="post" action="<?php echo htmlspecialchars(url_for('app/staff/libros.php')); ?>" style="display:inline">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo (int)$libro['id']; ?>">
                            <button type="submit" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este libro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>

</body>
</html>
