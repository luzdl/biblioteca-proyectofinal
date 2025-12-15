<?php
/**
 * Staff CRUD: Editar libro.
 *
 * Notes:
 * - Validates required fields via Input/Validator.
 * - Supports optional cover upload to public/img/portadas.
 * - When schema supports it, links libros.portada_upload_id to uploads.id.
 */
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

function libros_portada_limit_bytes(): int
{
    $dir = __DIR__ . '/../../..' . '/img/portadas';
    $max = 0;
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        if (is_array($files)) {
            foreach ($files as $f) {
                if (is_file($f)) {
                    $sz = @filesize($f);
                    if (is_int($sz) && $sz > $max) {
                        $max = $sz;
                    }
                }
            }
        }
    }
    $base = max($max, 15 * 1024 * 1024);
    return (int)ceil($base * 1.25);
}

function libros_portada_mime_allowed(string $mime): bool
{
    $mime = strtolower(trim($mime));
    return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
}

function libros_has_column(PDO $db, string $table, string $column): bool
{
    try {
        $stmt = $db->prepare("SHOW COLUMNS FROM {$table} LIKE :col");
        $stmt->execute([':col' => $column]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}

function libros_create_upload(PDO $db, array $archivo, int $usuarioId, string $storedName, string $relativePath, string $mime, int $sizeBytes, ?string $sha256): ?int
{
    try {
        $stmt = $db->prepare('INSERT INTO uploads (usuario_id, original_name, stored_name, relative_path, mime_type, size_bytes, sha256) VALUES (:uid, :orig, :stored, :path, :mime, :size, :sha)');
        $stmt->execute([
            ':uid' => $usuarioId,
            ':orig' => (string)($archivo['name'] ?? ''),
            ':stored' => $storedName,
            ':path' => $relativePath,
            ':mime' => $mime,
            ':size' => $sizeBytes,
            ':sha' => $sha256,
        ]);
        return (int)$db->lastInsertId();
    } catch (Exception $e) {
        return null;
    }
}

/* Obtener ID del libro */
if (!isset($_GET["id"])) {
    header('Location: ' . url_for('app/staff/libros.php'));
    exit;
}

$id = intval($_GET["id"]);

/* Cargar datos del libro */
$hasUploadCol = libros_has_column($db, 'libros', 'portada_upload_id');

if ($hasUploadCol) {
    $stmt = $db->prepare("
        SELECT
            l.*,
            u.relative_path AS portada_path
        FROM libros l
        LEFT JOIN uploads u ON u.id = l.portada_upload_id
        WHERE l.id = :id
        LIMIT 1
    ");
} else {
    $stmt = $db->prepare("
        SELECT
            l.*,
            (
                SELECT u.relative_path
                FROM uploads u
                WHERE u.stored_name = l.portada
                ORDER BY u.id DESC
                LIMIT 1
            ) AS portada_path
        FROM libros l
        WHERE l.id = :id
        LIMIT 1
    ");
}
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

    $titulo = Input::postString('titulo');
    $autor = Input::postString('autor');
    $categoria_id = Input::postInt('categoria_id', 0);
    $descripcion = Input::postString('descripcion');
    $stock = Input::postInt('stock', 0);

    $v = new Validator();
    $v->required('titulo', $titulo, 'El título es obligatorio.');
    $v->required('autor', $autor, 'El autor es obligatorio.');
    $v->minInt('categoria_id', $categoria_id, 1, 'Debe seleccionar una categoría válida.');
    $v->minInt('stock', $stock, 0, 'El stock no puede ser negativo.');

    if (!$v->ok()) {
        $mensaje = "Completa todos los campos obligatorios.";
    } else {

        $portadaNueva = $libro["portada"]; // Mantener la actual
        $portadaUploadId = $hasUploadCol ? (int)($libro['portada_upload_id'] ?? 0) : 0;

        /* ¿Subieron una nueva portada? */
        if (!empty($_FILES["portada"]["name"])) {

            $archivo = $_FILES["portada"];
            $ext = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));

            $permitidas = ["jpg", "jpeg", "png", "webp"];

            $maxBytes = libros_portada_limit_bytes();
            $maxMb = number_format($maxBytes / 1048576, 2);

            $uploadErr = (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
                $mensaje = "Archivo demasiado grande. Máximo " . $maxMb . " MB.";
            } elseif ($uploadErr !== UPLOAD_ERR_OK) {
                $mensaje = "No se pudo subir la imagen (error de carga).";
            } elseif (!in_array($ext, $permitidas, true)) {
                $mensaje = "Formato de imagen no permitido.";
            } else {
                $sizeBytes = (int)($archivo['size'] ?? 0);
                if ($sizeBytes <= 0) {
                    $mensaje = "La imagen está vacía o no es válida.";
                } elseif ($sizeBytes > $maxBytes) {
                    $mensaje = "Archivo demasiado grande. Máximo " . $maxMb . " MB.";
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = (string)$finfo->file((string)($archivo['tmp_name'] ?? ''));
                    if (!libros_portada_mime_allowed($mime)) {
                        $mensaje = "Formato de imagen no permitido.";
                    } else {
                        $nombreNuevo = uniqid("libro_") . "." . $ext;
                        $destDir = __DIR__ . "/../../.." . "/img/portadas/";
                        if (!is_dir($destDir)) {
                            @mkdir($destDir, 0775, true);
                        }
                        $destPath = $destDir . $nombreNuevo;

                        $sha256 = null;
                        try {
                            $sha256 = hash_file('sha256', (string)($archivo['tmp_name'] ?? ''));
                        } catch (Exception $e) {
                            $sha256 = null;
                        }

                        if (!move_uploaded_file((string)$archivo["tmp_name"], $destPath)) {
                            $mensaje = "No se pudo guardar la imagen en el servidor.";
                        } else {
                            $portadaNueva = $nombreNuevo;
                            $relativePath = 'img/portadas/' . $nombreNuevo;
                            $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
                            $newUploadId = libros_create_upload($db, $archivo, $usuarioId, $nombreNuevo, $relativePath, $mime, $sizeBytes, $sha256);
                            if ($hasUploadCol && $newUploadId) {
                                $portadaUploadId = $newUploadId;
                            }
                        }
                    }
                }
            }
        }

        if ($mensaje === '') {
            if ($hasUploadCol) {
                $sql = "UPDATE libros
                        SET titulo = :titulo,
                            autor = :autor,
                            categoria_id = :categoria_id,
                            descripcion = :descripcion,
                            portada = :portada,
                            portada_upload_id = :portada_upload_id,
                            stock = :stock
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ":titulo" => $titulo,
                    ":autor" => $autor,
                    ":categoria_id" => $categoria_id,
                    ":descripcion" => $descripcion,
                    ":portada" => $portadaNueva,
                    ":portada_upload_id" => $portadaUploadId > 0 ? $portadaUploadId : null,
                    ":stock" => $stock,
                    ":id" => $id
                ]);
            } else {
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
            }

            header('Location: ' . url_for('app/staff/libros.php', ['editado' => 1]));
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar libro</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/bibliotecario.css')); ?>">
</head>

<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

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
                <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="edit-portada">
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
