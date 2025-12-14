<?php
session_start();

require_once __DIR__ . '/../config/router.php';

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login');
    exit;
}

$usuario = [
    "nombre" => $_SESSION['usuario_usuario'],
    "email"  => $_SESSION['usuario_email'],
    "rol"    => $_SESSION['usuario_rol']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/student_reservas.css">
    <link rel="stylesheet" href="../css/perfil_estudiante.css">
</head>

<body>

<?php include 'components/sidebar.php'; ?>

<main class="content">

    <h1 class="title">Mi perfil</h1>
    <h2 class="subtitle">Información de tu cuenta</h2>

    <section class="profile-card">

        <div class="profile-avatar">
            <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
        </div>

        <div class="profile-info">
            <div class="info-item">
                <span class="label">Nombre</span>
                <span class="value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
            </div>

            <div class="info-item">
                <span class="label">Correo electrónico</span>
                <span class="value"><?php echo htmlspecialchars($usuario['email']); ?></span>
            </div>

            <div class="info-item">
                <span class="label">Rol</span>
                <span class="badge">Estudiante</span>
            </div>
        </div>

    </section>

</main>

</body>
</html>
