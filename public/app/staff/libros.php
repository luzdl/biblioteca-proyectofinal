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
$hasUploadCol = false;
try {
    $col = $db->prepare("SHOW COLUMNS FROM libros LIKE :col");
    $col->execute([':col' => 'portada_upload_id']);
    $hasUploadCol = (bool)$col->fetch();
} catch (Exception $e) {
    $hasUploadCol = false;
}

if ($hasUploadCol) {
    $query = "
        SELECT
            l.id,
            l.titulo,
            l.autor,
            l.portada,
            l.portada_upload_id,
            l.stock,
            c.nombre AS categoria,
            u.relative_path AS portada_path
        FROM libros l
        INNER JOIN categorias_libros c ON c.id = l.categoria_id
        LEFT JOIN uploads u ON u.id = l.portada_upload_id
        ORDER BY l.titulo ASC
    ";
} else {
    $query = "
        SELECT
            l.id,
            l.titulo,
            l.autor,
            l.portada,
            l.stock,
            c.nombre AS categoria,
            (
                SELECT u.relative_path
                FROM uploads u
                WHERE u.stored_name = l.portada
                ORDER BY u.id DESC
                LIMIT 1
            ) AS portada_path
        FROM libros l
        INNER JOIN categorias_libros c ON c.id = l.categoria_id
        ORDER BY l.titulo ASC
    ";
}

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

    <div class="table-wrap">
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
                        <?php
                            $portadaPath = (string)($libro['portada_path'] ?? '');
                            $portadaFile = (string)($libro['portada'] ?? '');
                            $imgUrl = '';
                            if ($portadaPath !== '') {
                                $imgUrl = url_for(ltrim($portadaPath, '/'));
                            } elseif ($portadaFile !== '') {
                                $imgUrl = url_for('img/portadas/' . ltrim($portadaFile, '/'));
                            }
                        ?>
                        <?php if ($imgUrl !== ''): ?>
                            <img 
                                src="<?php echo htmlspecialchars($imgUrl); ?>" 
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

                    <td>
                        <div class="actions">
                            <a class="btn-edit" href="<?php echo htmlspecialchars(url_for('app/staff/libros_editar.php', ['id' => $libro['id']])); ?>">Editar</a>
                            <form method="post" action="<?php echo htmlspecialchars(url_for('app/staff/libros.php')); ?>" style="display:inline">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo (int)$libro['id']; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este libro?')">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    </div>

</main>

</body>
</html>
