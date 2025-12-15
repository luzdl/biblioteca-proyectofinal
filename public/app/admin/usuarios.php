<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador']);

$db = (new Database())->getConnection();

$mensaje = '';
$tipoMensaje = '';
$fieldErrors = [];

// Get available roles
$rolesStmt = $db->query("SELECT id, name FROM roles ORDER BY name");
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

// 1) DELETE USER
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    
    // Prevent self-deletion
    if ($id === (int)($_SESSION['usuario_id'] ?? 0)) {
        $mensaje = "No puedes eliminar tu propia cuenta.";
        $tipoMensaje = "error";
    } elseif ($id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $mensaje = "Usuario eliminado correctamente.";
            $tipoMensaje = "exito";
        } catch (Exception $e) {
            $mensaje = "No se pudo eliminar el usuario.";
            $tipoMensaje = "error";
        }
    }
}

// 2) LOAD USER FOR EDITING
$usuarioEditar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, usuario, email, rol FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $usuarioEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 3) CREATE OR UPDATE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = Input::postInt('id', 0);
    $usuario = Input::postString('usuario');
    $email = Input::postString('email');
    $rol = Input::postString('rol');
    $password = Input::postRawString('password', '');

    $v = new Validator();
    $v->required('usuario', $usuario, 'El nombre de usuario es obligatorio.');
    $v->required('email', $email, 'El email es obligatorio.');
    $v->email('email', $email, 'El email no es válido.');
    $v->required('rol', $rol, 'Debe seleccionar un rol.');
    if ($id === 0) {
        $v->required('password', $password, 'La contraseña es obligatoria para nuevos usuarios.');
    }

    $fieldErrors = $v->errors();

    if (!$v->ok()) {
        $mensaje = implode(' ', array_values($fieldErrors));
        $tipoMensaje = 'error';
        // Keep form data
        $usuarioEditar = [
            'id' => $id,
            'usuario' => $usuario,
            'email' => $email,
            'rol' => $rol
        ];
    } else {
        try {
            // Check for duplicate username/email
            $checkSql = "SELECT id FROM usuarios WHERE (usuario = :usuario OR email = :email)";
            if ($id > 0) {
                $checkSql .= " AND id != :id";
            }
            $checkStmt = $db->prepare($checkSql);
            $params = [':usuario' => $usuario, ':email' => $email];
            if ($id > 0) $params[':id'] = $id;
            $checkStmt->execute($params);
            
            if ($checkStmt->fetch()) {
                $fieldErrors['usuario'] = "Ya existe un usuario con ese nombre.";
                $fieldErrors['email'] = "Ya existe un usuario con ese email.";
                $mensaje = "Ya existe un usuario con ese nombre o email.";
                $tipoMensaje = "error";
                $usuarioEditar = ['id' => $id, 'usuario' => $usuario, 'email' => $email, 'rol' => $rol];
            } else {
                if ($id > 0) {
                    // Update
                    if ($password !== '') {
                        $sql = "UPDATE usuarios SET usuario = :usuario, email = :email, rol = :rol, password_hash = :password_hash WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            ':usuario' => $usuario,
                            ':email' => $email,
                            ':rol' => $rol,
                            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                            ':id' => $id
                        ]);
                    } else {
                        $sql = "UPDATE usuarios SET usuario = :usuario, email = :email, rol = :rol WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            ':usuario' => $usuario,
                            ':email' => $email,
                            ':rol' => $rol,
                            ':id' => $id
                        ]);
                    }
                    
                    // Sync usuario_roles table
                    $roleIdStmt = $db->prepare("SELECT id FROM roles WHERE name = :name");
                    $roleIdStmt->execute([':name' => $rol]);
                    $roleRow = $roleIdStmt->fetch();
                    if ($roleRow) {
                        $db->prepare("DELETE FROM usuario_roles WHERE usuario_id = :uid")->execute([':uid' => $id]);
                        $db->prepare("INSERT INTO usuario_roles (usuario_id, role_id) VALUES (:uid, :rid)")
                           ->execute([':uid' => $id, ':rid' => $roleRow['id']]);
                    }
                    
                    $mensaje = "Usuario actualizado correctamente.";
                    $tipoMensaje = "exito";
                    $usuarioEditar = null;
                } else {
                    // Create
                    $sql = "INSERT INTO usuarios (usuario, email, password_hash, rol) VALUES (:usuario, :email, :password_hash, :rol)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':usuario' => $usuario,
                        ':email' => $email,
                        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ':rol' => $rol
                    ]);
                    
                    $newUserId = $db->lastInsertId();
                    
                    // Sync usuario_roles table
                    $roleIdStmt = $db->prepare("SELECT id FROM roles WHERE name = :name");
                    $roleIdStmt->execute([':name' => $rol]);
                    $roleRow = $roleIdStmt->fetch();
                    if ($roleRow) {
                        $db->prepare("INSERT INTO usuario_roles (usuario_id, role_id) VALUES (:uid, :rid)")
                           ->execute([':uid' => $newUserId, ':rid' => $roleRow['id']]);
                    }
                    
                    $mensaje = "Usuario creado correctamente.";
                    $tipoMensaje = "exito";
                    $usuarioEditar = null;
                }
            }
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al guardar el usuario.";
            $tipoMensaje = "error";
        }
    }
}

// 4) LIST ALL USERS
$stmt = $db->query("SELECT id, usuario, email, rol, created_at FROM usuarios ORDER BY usuario");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios | Biblioteca Digital</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin_usuarios.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content usuarios-page">
    <h1 class="title">Gestión de usuarios</h1>

    <?php if ($mensaje): ?>
        <p class="alert alert-<?php echo $tipoMensaje === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <section class="form-section">
        <h2><?php echo $usuarioEditar ? 'Editar usuario' : 'Nuevo usuario'; ?></h2>

        <form method="post" action="" class="crud-form usuarios-form">
            <input type="hidden" name="id" value="<?php echo $usuarioEditar['id'] ?? 0; ?>">
            
            <label class="usuarios-field">
                <span class="usuarios-label">Nombre de usuario:<span class="usuarios-required">*</span></span>
                <input class="usuarios-input <?php echo isset($fieldErrors['usuario']) ? 'usuarios-input--error' : ''; ?>" type="text" name="usuario" value="<?php echo htmlspecialchars($usuarioEditar['usuario'] ?? ''); ?>" required <?php echo isset($fieldErrors['usuario']) ? 'aria-invalid="true"' : ''; ?>>
                <?php if (isset($fieldErrors['usuario'])): ?>
                    <small class="usuarios-error"><?php echo htmlspecialchars($fieldErrors['usuario']); ?></small>
                <?php endif; ?>
            </label>

            <label class="usuarios-field">
                <span class="usuarios-label">Email:<span class="usuarios-required">*</span></span>
                <input class="usuarios-input <?php echo isset($fieldErrors['email']) ? 'usuarios-input--error' : ''; ?>" type="email" name="email" value="<?php echo htmlspecialchars($usuarioEditar['email'] ?? ''); ?>" required <?php echo isset($fieldErrors['email']) ? 'aria-invalid="true"' : ''; ?>>
                <?php if (isset($fieldErrors['email'])): ?>
                    <small class="usuarios-error"><?php echo htmlspecialchars($fieldErrors['email']); ?></small>
                <?php endif; ?>
            </label>

            <label class="usuarios-field">
                <span class="usuarios-label">Contraseña<?php echo $usuarioEditar ? ' (dejar vacío para no cambiar)' : ':<span class="usuarios-required">*</span>'; ?></span>
                <input class="usuarios-input <?php echo isset($fieldErrors['password']) ? 'usuarios-input--error' : ''; ?>" type="password" name="password" <?php echo $usuarioEditar ? '' : 'required'; ?> <?php echo isset($fieldErrors['password']) ? 'aria-invalid="true"' : ''; ?>>
                <?php if (isset($fieldErrors['password'])): ?>
                    <small class="usuarios-error"><?php echo htmlspecialchars($fieldErrors['password']); ?></small>
                <?php endif; ?>
            </label>

            <label class="usuarios-field">
                <span class="usuarios-label">Rol:<span class="usuarios-required">*</span></span>
                <select class="usuarios-select <?php echo isset($fieldErrors['rol']) ? 'usuarios-select--error' : ''; ?>" name="rol" required <?php echo isset($fieldErrors['rol']) ? 'aria-invalid="true"' : ''; ?>>
                    <option value="">-- Seleccionar rol --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?php echo htmlspecialchars($r['name']); ?>" <?php echo ($usuarioEditar['rol'] ?? '') === $r['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($r['name'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($fieldErrors['rol'])): ?>
                    <small class="usuarios-error"><?php echo htmlspecialchars($fieldErrors['rol']); ?></small>
                <?php endif; ?>
            </label>

            <div class="form-actions usuarios-actions">
                <button class="usuarios-btn usuarios-btn--primary" type="submit"><?php echo $usuarioEditar ? 'Actualizar' : 'Crear usuario'; ?></button>
                <?php if ($usuarioEditar): ?>
                    <a href="<?php echo htmlspecialchars(url_for('app/admin/usuarios.php')); ?>" class="usuarios-btn usuarios-btn--ghost">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="list-section">
        <h2>Listado de usuarios</h2>

        <?php if (count($usuarios) === 0): ?>
            <p>No hay usuarios registrados.</p>
        <?php else: ?>
            <table class="crud-table usuarios-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($u['rol'])); ?></td>
                        <td class="usuarios-table-actions">
                            <a class="usuarios-link" href="<?php echo htmlspecialchars(url_for('app/admin/usuarios.php', ['editar' => $u['id']])); ?>">Editar</a>
                            <?php if ((int)$u['id'] !== (int)($_SESSION['usuario_id'] ?? 0)): ?>
                            |
                            <a class="usuarios-link" href="<?php echo htmlspecialchars(url_for('app/admin/usuarios.php', ['eliminar' => $u['id']])); ?>"
                                onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
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
