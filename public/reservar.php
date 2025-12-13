<?php
session_start();

/* Verificar rol */
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: login.php");
    exit;
}

$id = $_GET["id"] ?? null;

if (!$id) {
    die("ID de libro no válido.");
}

/* Simulación de reserva */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva confirmada</title>
    <link rel="stylesheet" href="../css/student.css">
</head>

<body>

<div class="modal-bg">
    <div class="modal-box">
        <h2>Reserva realizada</h2>
        <p>Has reservado el libro con ID: <strong><?php echo htmlspecialchars($id); ?></strong></p>

        <a class="btn" href="student_only.php">Volver al catálogo</a>
    </div>
</div>

</body>
</html>
