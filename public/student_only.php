<?php
session_start();

// Solo estudiantes
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();

    $sql = "SELECT libros.id, libros.titulo, libros.autor, categorias.nombre AS categoria,
                   libros.stock
            FROM libros
            INNER JOIN categorias ON libros.categoria_id = categorias.id";

    $stmt = $db->query($sql);
    $libros = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error al cargar libros: " . $e->getMessage());
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

<!-- BARRA SUPERIOR -->
<div class="header">
    <div class="username">Hola, <?php echo htmlspecialchars($_SESSION['usuario_usuario']); ?> </div>
    <a href="logout.php">Cerrar sesión</a>
</div>

<div class="container">

    <!-- Buscador -->
    <form method="GET" class="search-bar">
        <input type="text" name="q" placeholder="Buscar por título, autor o categoría…">
        <button type="submit">Buscar</button>
    </form>

    <!-- Título -->
    <h2>Libros disponibles</h2>

    <!-- GRID DE LIBROS -->
    <div class="book-grid">

        <?php foreach ($libros as $libro): ?>
            <div class="book-card">

                <div class="book-title"><?= htmlspecialchars($libro['titulo']) ?></div>
                <div class="book-author"><?= htmlspecialchars($libro['autor']) ?></div>

                <div class="book-category"><?= htmlspecialchars($libro['categoria']) ?></div>

                <div class="book-stock">
                    Stock: 
                    <?php echo $libro['stock'] > 0 
                        ? $libro['stock']
                        : "<span class='out-of-stock'>Agotado</span>"; ?>
                </div>

                <?php if ($libro['stock'] > 0): ?>
                    <a href="reservar.php?id=<?= $libro['id'] ?>">Reservar</a>
                <?php else: ?>
                    <div class="out-of-stock">No disponible</div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>
