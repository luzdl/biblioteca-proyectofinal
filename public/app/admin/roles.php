<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador']);

$db = (new Database())->getConnection();

$mensaje = '';
$tipoMensaje = '';

// 1) DELETE ROLE
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    
    if ($id > 0) {
        // Check if role is in use
        $checkStmt = $db->prepare("SELECT COUNT(*) as cnt FROM usuario_roles WHERE role_id = :id");
        $checkStmt->execute([':id' => $id]);
        $inUse = (int)$checkStmt->fetch()['cnt'];
        
        // Also check usuarios.rol
        $roleNameStmt = $db->prepare("SELECT name FROM roles WHERE id = :id");
        $roleNameStmt->execute([':id' => $id]);
        $roleRow = $roleNameStmt->fetch();
        
        if ($roleRow) {
            $checkUsuariosStmt = $db->prepare("SELECT COUNT(*) as cnt FROM usuarios WHERE rol = :name");
            $checkUsuariosStmt->execute([':name' => $roleRow['name']]);
            $inUse += (int)$checkUsuariosStmt->fetch()['cnt'];
        }
        
        if ($inUse > 0) {
            $mensaje = "No se puede eliminar el rol porque está asignado a usuarios.";
            $tipoMensaje = "error";
        } else {
            try {
                $stmt = $db->prepare("DELETE FROM roles WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mensaje = "Rol eliminado correctamente.";
                $tipoMensaje = "exito";
            } catch (Exception $e) {
                $mensaje = "No se pudo eliminar el rol.";
                $tipoMensaje = "error";
            }
        }
    }
}

// 2) LOAD ROLE FOR EDITING
$rolEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, name, description FROM roles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $rolEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 3) CREATE OR UPDATE ROLE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $mensaje = "El nombre del rol es obligatorio.";
        $tipoMensaje = "error";
        $rolEditar = ['id' => $id, 'name' => $name, 'description' => $description];
    } else {
        try {
            // Check for duplicate name
            $checkSql = "SELECT id FROM roles WHERE name = :name";
            if ($id > 0) {
                $checkSql .= " AND id != :id";
            }
            $checkStmt = $db->prepare($checkSql);
            $params = [':name' => $name];
            if ($id > 0) $params[':id'] = $id;
            $checkStmt->execute($params);
            
            if ($checkStmt->fetch()) {
                $mensaje = "Ya existe un rol con ese nombre.";
                $tipoMensaje = "error";
                $rolEditar = ['id' => $id, 'name' => $name, 'description' => $description];
            } else {
                if ($id > 0) {
                    // Get old name to update usuarios.rol
                    $oldNameStmt = $db->prepare("SELECT name FROM roles WHERE id = :id");
                    $oldNameStmt->execute([':id' => $id]);
                    $oldName = $oldNameStmt->fetchColumn();
                    
                    // Update role
                    $sql = "UPDATE roles SET name = :name, description = :description WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':name' => $name, ':description' => $description, ':id' => $id]);
                    
                    // Sync usuarios.rol if name changed
                    if ($oldName && $oldName !== $name) {
                        $db->prepare("UPDATE usuarios SET rol = :newname WHERE rol = :oldname")
                           ->execute([':newname' => $name, ':oldname' => $oldName]);
                    }
                    
                    $mensaje = "Rol actualizado correctamente.";
                    $tipoMensaje = "exito";
                    $rolEditar = null;
                } else {
                    // Create
                    $sql = "INSERT INTO roles (name, description) VALUES (:name, :description)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':name' => $name, ':description' => $description]);
                    $mensaje = "Rol creado correctamente.";
                    $tipoMensaje = "exito";
                    $rolEditar = null;
                }
            }
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar el rol.";
            $tipoMensaje = "error";
        }
    }
}

// 4) LIST ALL ROLES with user count
$stmt = $db->query("
    SELECT r.id, r.name, r.description,
           (SELECT COUNT(*) FROM usuarios WHERE rol = r.name) as user_count
    FROM roles r
    ORDER BY r.name
");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Roles | Biblioteca Digital</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>

<main class="content">
    <h1 class="title">Gestión de roles</h1>

    <?php if ($mensaje): ?>
        <p class="alert alert-<?php echo $tipoMensaje === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <section class="form-section">
        <h2><?php echo $rolEditar ? 'Editar rol' : 'Nuevo rol'; ?></h2>

        <form method="post" action="" class="crud-form">
            <input type="hidden" name="id" value="<?php echo $rolEditar['id'] ?? 0; ?>">
            
            <label>
                Nombre del rol:
                <input type="text" name="name" value="<?php echo htmlspecialchars($rolEditar['name'] ?? ''); ?>" required>
            </label>

            <label>
                Descripción:
                <input type="text" name="description" value="<?php echo htmlspecialchars($rolEditar['description'] ?? ''); ?>">
            </label>

            <div class="form-actions">
                <button type="submit"><?php echo $rolEditar ? 'Actualizar' : 'Crear rol'; ?></button>
                <?php if ($rolEditar): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/admin/roles.php')); ?>" class="btn-cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="list-section">
        <h2>Listado de roles</h2>

        <?php if (count($roles) === 0): ?>
            <p>No hay roles registrados.</p>
        <?php else: ?>
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($roles as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo htmlspecialchars($r['description'] ?? ''); ?></td>
                        <td><?php echo (int)$r['user_count']; ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(url_for('app/admin/roles.php', ['editar' => $r['id']])); ?>">Editar</a>
                            <?php if ((int)$r['user_count'] === 0): ?>
                            |
                            <a href="<?php echo htmlspecialchars(url_for('app/admin/roles.php', ['eliminar' => $r['id']])); ?>"
                               onclick="return confirm('¿Seguro que deseas eliminar este rol?');">
                               Eliminar
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
