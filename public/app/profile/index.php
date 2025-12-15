<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_login();

$db = (new Database())->getConnection();
$user = fetch_current_user($db);

$mensaje = '';
$error   = '';

/* ==========================
   CAMBIO DE CREDENCIALES
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_credenciales'])) {

    $nuevoUsuario = trim($_POST['nuevo_usuario'] ?? '');
    $actual       = $_POST['password_actual'] ?? '';
    $nueva        = $_POST['password_nueva'] ?? '';
    $confirmar    = $_POST['password_confirmar'] ?? '';

    if ($nuevoUsuario === '' || $actual === '' || $nueva === '' || $confirmar === '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($nueva) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {

        $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);
        $hashActual = $stmt->fetchColumn();

        if (!password_verify($actual, $hashActual)) {
            $error = 'La contraseña actual es incorrecta.';
        } else {

            $check = $db->prepare("
                SELECT COUNT(*) FROM usuarios 
                WHERE usuario = :u AND id != :id
            ");
            $check->execute([
                ':u' => $nuevoUsuario,
                ':id' => $user['id']
            ]);

            if ($check->fetchColumn() > 0) {
                $error = 'Ese nombre de usuario ya existe.';
            } else {

                $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);

                $upd = $db->prepare("
                    UPDATE usuarios 
                    SET usuario = :u, password = :p
                    WHERE id = :id
                ");
                $upd->execute([
                    ':u' => $nuevoUsuario,
                    ':p' => $nuevoHash,
                    ':id'=> $user['id']
                ]);

                $_SESSION['usuario_usuario'] = $nuevoUsuario;
                $user['usuario'] = $nuevoUsuario;

                $mensaje = 'Credenciales actualizadas correctamente.';
            }
        }
    }
}

/* ==========================
   DATOS PERFIL
========================== */
$profileData = $user ?: [
    'usuario' => $_SESSION['usuario_usuario'] ?? '',
    'email'   => $_SESSION['usuario_email'] ?? '',
    'rol'     => $_SESSION['usuario_rol'] ?? '',
];

$inicial = strtoupper(substr($profileData['usuario'] ?? 'U', 0, 1));
$rol = (string)($profileData['rol'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/profile.css')); ?>">
</head>

<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1 class="title">Mi perfil</h1>

    <!-- PERFIL -->
    <section class="profile-card">
        <div class="profile-avatar">
            <span class="avatar-letter"><?php echo htmlspecialchars($inicial); ?></span>
        </div>

        <div class="profile-info">
            <h2><?php echo htmlspecialchars($profileData['usuario']); ?></h2>
            <p><?php echo htmlspecialchars($profileData['email']); ?></p>
            <span class="badge"><?php echo ucfirst(htmlspecialchars($rol)); ?></span>
        </div>
    </section>

    <!-- ACCIONES -->
    <section class="profile-actions">
        <h2 class="subtitle">Acciones</h2>
        <ul>
            <?php if ($rol === 'administrador'): ?>
                <li><a href="<?php echo url_for('app/admin/categorias.php'); ?>">Gestionar categorías</a></li>
                <li><a href="<?php echo url_for('app/admin/carreras.php'); ?>">Gestionar carreras</a></li>
            <?php endif; ?>

            <?php if ($rol === 'bibliotecario'): ?>
                <li><a href="<?php echo url_for('app/admin/carreras.php'); ?>">Gestionar carreras</a></li>
            <?php endif; ?>

            <?php if ($rol === 'estudiante'): ?>
                <li><a href="<?php echo url_for('app/student/reservas.php'); ?>">Ver mis reservas</a></li>
                <li><a href="<?php echo url_for('app/student/historial.php'); ?>">Ver historial</a></li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- CAMBIO DE CREDENCIALES -->
    <section class="profile-actions">
        <p class="credentials-title">
            Cambia aquí tus credenciales
        </p>

        <?php if ($mensaje): ?>
            <p class="success-msg"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error-msg"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <button class="toggle-btn" onclick="toggleCredenciales()">
            Cambiar usuario o contraseña
        </button>

        <form method="POST" class="credentials-form" id="credentialsForm">
            <input type="hidden" name="cambiar_credenciales" value="1">

            <label>Nuevo usuario</label>
            <input type="text" name="nuevo_usuario"
                   value="<?php echo htmlspecialchars($profileData['usuario']); ?>" required>

            <label>Contraseña actual</label>
            <input type="password" name="password_actual" required>

            <label>Nueva contraseña</label>
            <input type="password" name="password_nueva" required>

            <label>Confirmar nueva contraseña</label>
            <input type="password" name="password_confirmar" required>

            <button type="submit" class="save-btn">Guardar cambios</button>
        </form>
    </section>
</main>

<script>
function toggleCredenciales() {
    document.getElementById('credentialsForm')
        .classList.toggle('open');
}
</script>

</body>
</html>
