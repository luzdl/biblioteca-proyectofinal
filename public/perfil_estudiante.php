<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: login.php");
    exit;
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
</head>

<body>

<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="../img/logo_redondo.png" alt="Logo">
        <h2>ReadOwl</h2>
    </div>

    <nav class="sidebar-menu">
        <a href="student_only.php">Catálogo</a>
        <a href="student_reservas.php">Mis reservas</a>
        <a href="student_historial.php">Historial</a>
        <a href="perfil_estudiante.php" class="active">Perfil</a>
    </nav>

    <a href="logout.php" class="logout-btn">Cerrar sesión</a>

    <div class="sidebar-user">
        <img src="../img/user_placeholder.png" alt="Usuario">
        <p><i><?php echo $usuario['nombre']; ?></i></p>
    </div>

</aside>

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
