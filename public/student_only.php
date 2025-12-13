<?php
session_start();

/* Verifica que el usuario esté autenticado y que sea estudiante */
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: login.php");
    exit;
}

/* Libros simulados mientras no haya base de datos */
$libros = [
    [
        "id" => 1,
        "titulo" => "Cien años de soledad",
        "autor" => "Gabriel García Márquez",
        "categoria" => "Novela",
        "stock" => 4,
        "imagen" => "../img/libro1.jpg"
    ],
    [
        "id" => 2,
        "titulo" => "1984",
        "autor" => "George Orwell",
        "categoria" => "Distopía",
        "stock" => 0,
        "imagen" => "../img/libro2.jpg"
    ],
    [
        "id" => 3,
        "titulo" => "El principito",
        "autor" => "Antoine de Saint-Exupéry",
        "categoria" => "Fábula",
        "stock" => 7,
        "imagen" => "../img/libro3.jpg"
    ],
    [
        "id" => 4,
        "titulo" => "Don Quijote de la Mancha",
        "autor" => "Miguel de Cervantes",
        "categoria" => "Clásico",
        "stock" => 2,
        "imagen" => "../img/libro4.jpg"
    ]
];

/* Buscador */
$busqueda = $_GET["q"] ?? "";

if ($busqueda !== "") {
    $libros = array_filter($libros, function($libro) use ($busqueda) {
        $q = strtolower($busqueda);
        return 
            str_contains(strtolower($libro["titulo"]), $q) ||
            str_contains(strtolower($libro["autor"]), $q) ||
            str_contains(strtolower($libro["categoria"]), $q);
    });
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca | Estudiante</title>
    <link rel="stylesheet" href="../css/student.css">
</head>

<body>

<!-- Barra superior -->
<header class="topbar">
    <div class="logo">Biblioteca Digital</div>

    <nav class="menu">
        <a href="student_only.php" class="active">Catálogo</a>
        <a href="student_reservas.php">Mis reservas</a>
        <a href="student_historial.php">Historial</a>
        <a href="perfil_estudiante.php">Perfil</a>
        <a href="logout.php" class="logout">Cerrar sesión</a>
    </nav>

    <div class="usuario">
        <?php echo htmlspecialchars($_SESSION['usuario_usuario']); ?>
    </div>
</header>


<div class="container">

    <!-- Buscador -->
    <form method="GET" class="search-box">
        <input 
            type="text" 
            name="q" 
            placeholder="Buscar por título, autor o categoría..." 
            value="<?php echo htmlspecialchars($busqueda); ?>"
        >
        <button type="submit">Buscar</button>
    </form>

    <h2 class="section-title">Libros disponibles</h2>

    <!-- Grid de libros -->
    <div class="book-grid">

        <?php if (empty($libros)): ?>
            <p>No se encontraron libros para la búsqueda seleccionada.</p>
        <?php endif; ?>

        <?php foreach ($libros as $libro): ?>
            <div class="book-card">

                <div class="book-image">
                    <img src="<?php echo htmlspecialchars($libro['imagen']); ?>" alt="Portada">
                </div>

                <h3 class="book-title">
                    <?php echo htmlspecialchars($libro['titulo']); ?>
                </h3>

                <p class="book-author"><?php echo htmlspecialchars($libro['autor']); ?></p>
                <p class="book-category"><?php echo htmlspecialchars($libro['categoria']); ?></p>

                <p class="book-stock">
                    Disponibilidad: 
                    <?php echo $libro['stock'] > 0 ? $libro['stock'] : '<span class="agotado">Agotado</span>'; ?>
                </p>

                <?php if ($libro['stock'] > 0): ?>
                    <a class="reserve-btn" href="reservar.php?id=<?php echo $libro['id']; ?>">
                        Reservar
                    </a>
                <?php else: ?>
                    <div class="reserve-disabled">No disponible</div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
        
    </div>

</div>

</body>
</html>
