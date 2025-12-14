<?php
session_start();

require_once __DIR__ . '/../config/router.php';

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login');
}

$usuario = [
    "nombre" => $_SESSION['usuario_usuario'],
    "email" => $_SESSION['usuario_email'],
    "rol" => $_SESSION['usuario_rol']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/student_reservas.css">
        <link rel="stylesheet" href="../css/student_reservas.css">
</head>

<body>

<?php include 'components/sidebar.php'; ?>

<main class="content">
    <h1 class="title">Mi perfil</h1>

    <section class="profile-box">
        <h2 class="subtitle">Información personal</h2>

        <p><b>Nombre:</b> <?php echo $usuario['nombre']; ?></p>
        <p><b>Email:</b> <?php echo $usuario['email']; ?></p>
        <p><b>Rol:</b> Estudiante</p>
    </section>

</main>

</body>
</html>
