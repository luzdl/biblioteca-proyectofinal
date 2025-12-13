<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: login.php");
    exit;
}

$historial = [
    [
        "titulo" => "Cien años de soledad",
        "autor" => "García Márquez",
        "fecha" => "2024-01-12",
        "imagen" => "../img/libro1.jpg"
    ],
    [
        "titulo" => "El Señor de los Anillos",
        "autor" => "J.R.R. Tolkien",
        "fecha" => "2024-02-01",
        "imagen" => "../img/libro_lotr.jpg"
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
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
        <a href="student_historial.php" class="active">Historial</a>
        <a href="perfil_estudiante.php">Perfil</a>
    </nav>

    <a href="logout.php" class="logout-btn">Cerrar sesión</a>

    <div class="sidebar-user">
        <img src="../img/user_placeholder.png" alt="Usuario">
        <p><i><?php echo $_SESSION['usuario_usuario']; ?></i></p>
    </div>
</aside>

<main class="content">

    <h1 class="title">Historial</h1>
    <h2 class="subtitle">Libros reservados anteriormente</h2>

    <div class="books-row">

        <?php foreach ($historial as $h): ?>
            <div class="book-card">
                <img src="<?php echo $h['imagen']; ?>" class="book-img">

                <h3 class="book-title"><?php echo $h['titulo']; ?></h3>
                <p class="author"><?php echo $h['autor']; ?></p>
                <p class="estado">Fecha: <?php echo $h['fecha']; ?></p>
            </div>
        <?php endforeach; ?>

    </div>

    <div class="shelf-line"></div>

</main>

</body>
</html>
