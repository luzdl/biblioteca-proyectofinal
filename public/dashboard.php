<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio | Biblioteca Digital</title>
</head>
<body>
    <h1>Bienvenido a la Biblioteca Digital</h1>
    <p>
        Has iniciado sesión como 
        <strong><?php echo htmlspecialchars($_SESSION['usuario_usuario'] ?? $_SESSION['usuario_email'] ?? ''); ?></strong>.
    </p>

    <p><a href="logout.php">Cerrar sesión</a></p>
</body>
</html>
