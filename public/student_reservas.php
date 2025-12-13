<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: login.php");
    exit;
}

$reservas = [
    [
        "id" => 1,
        "titulo" => "Cien años de soledad",
        "autor" => "García Márquez",
        "estado" => "En curso",
        "imagen" => "../img/libro1.jpg"
    ],
    [
        "id" => 2,
        "titulo" => "El Señor de los Anillos",
        "autor" => "J.R.R. Tolkien",
        "estado" => "Pendiente",
        "imagen" => "../img/libro_lotr.jpg"
    ],
    [
        "id" => 3,
        "titulo" => "1984",
        "autor" => "George Orwell",
        "estado" => "Finalizado",
        "imagen" => "../img/libro_1984.jpg"
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis reservas</title>
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
        <a href="student_reservas.php" class="active">Mis reservas</a>
        <a href="student_historial.php">Historial</a>
        <a href="perfil_estudiante.php">Perfil</a>
    </nav>

    <a href="logout.php" class="logout-btn">Cerrar sesión</a>

    <div class="sidebar-user">
        <img src="../img/user_placeholder.png" alt="Usuario">
        <p><i><?php echo htmlspecialchars($_SESSION['usuario_usuario']); ?></i></p>
    </div>
</aside>

<main class="content">

    <h1 class="title">Mis reservas</h1>

    <section class="shelf">
        <h2 class="subtitle">Actualmente reservados</h2>

        <div class="books-row">

            <?php foreach ($reservas as $reserva): ?>
                <div class="book-card">

                    <img src="<?php echo $reserva['imagen']; ?>" 
                         class="book-img" alt="Portada">

                    <h3 class="book-title"><?php echo $reserva['titulo']; ?></h3>

                    <p class="author"><?php echo $reserva['autor']; ?></p>

                    <p class="estado estado-<?php echo strtolower(str_replace(' ', '', $reserva['estado'])); ?>">
                        <?php echo $reserva['estado']; ?>
                    </p>

                    <a href="cancelar_reserva.php?id=<?php echo $reserva['id']; ?>"
                       class="cancel-btn">
                       Cancelar reserva
                    </a>

                </div>
            <?php endforeach; ?>

        </div>

        <div class="shelf-line"></div>
    </section>

</main>

</body>
</html>
