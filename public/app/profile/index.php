<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_login();

$db = (new Database())->getConnection();
$user = fetch_current_user($db);

$upload = null;
if ($user && isset($user['profile_upload_id']) && $user['profile_upload_id']) {
    $upload = fetch_profile_upload($db, (int)$user['profile_upload_id']);
}

$profileData = $user ?: [
    'usuario' => $_SESSION['usuario_usuario'] ?? '',
    'email' => $_SESSION['usuario_email'] ?? '',
    'rol' => $_SESSION['usuario_rol'] ?? '',
];

if ($upload && isset($upload['relative_path'])) {
    $profileData['relative_path'] = $upload['relative_path'];
}

$rol = (string)($profileData['rol'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_reservas.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>

<main class="content">
    <h1 class="title">Mi perfil</h1>

    <?php
        $userForCard = $profileData;
        include __DIR__ . '/../../components/profile_card.php';
    ?>

    <section class="profile-box">
        <h2 class="subtitle">Acciones</h2>

        <?php if ($rol === 'administrador'): ?>
            <p><a href="<?php echo htmlspecialchars(url_for('app/admin/categorias.php')); ?>">Gestionar categorías</a></p>
            <p><a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>">Gestionar carreras</a></p>
        <?php endif; ?>

        <?php if ($rol === 'bibliotecario'): ?>
            <p><a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>">Gestionar carreras</a></p>
        <?php endif; ?>

        <?php if ($rol === 'estudiante'): ?>
            <p><a href="<?php echo htmlspecialchars(url_for('app/student/reservas.php')); ?>">Ver mis reservas</a></p>
            <p><a href="<?php echo htmlspecialchars(url_for('app/student/historial.php')); ?>">Ver historial</a></p>
        <?php endif; ?>

    </section>
</main>

</body>
</html>
