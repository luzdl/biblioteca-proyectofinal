<?php
session_start();

require_once __DIR__ . '/../config/router.php';
require_once __DIR__ . '/../config/database.php';

/* Verifica rol */
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login');
}

$db = (new Database())->getConnection();

/* Buscador */
$busqueda = trim($_GET['q'] ?? '');

if ($busqueda !== '') {
    $sql = "SELECT * FROM libros
            WHERE titulo LIKE :q
               OR autor LIKE :q
               OR categoria LIKE :q";
    $stmt = $db->prepare($sql);
    $stmt->execute([':q' => "%$busqueda%"]);
} else {
    $sql = "SELECT * FROM libros";
    $stmt = $db->query($sql);
}

$libros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca | Estudiante</title>
    <link rel="stylesheet" href="../css/student.css">
    <link rel="stylesheet" href="../css/components/book_card.css">
    <link rel="stylesheet" href="../css/topbar-dropdown.css">
</head>

<body>

<header class="topbar">
    <div class="logo">Biblioteca Digital</div>

    <nav class="menu">
        <a href="student_only.php" class="active">Catálogo</a>
        <a href="student_reservas.php">Mis reservas</a>
        <a href="student_historial.php">Historial</a>
    </nav>

    <?php include __DIR__ . '/components/topbar_dropdown.php'; ?>
</header>

<div class="container">

    <form method="GET" class="search-box">
        <input
            type="text"
            name="q"
            placeholder="Buscar por título, autor o categoría..."
            value="<?= htmlspecialchars($busqueda) ?>"
        >
        <button type="submit">Buscar</button>
    </form>

    <h2 class="section-title">Libros disponibles</h2>

    <div class="book-grid">

        <?php if (empty($libros)): ?>
            <p>No se encontraron libros.</p>
        <?php endif; ?>

        <?php foreach ($libros as $libro): ?>
            <?php
                $book = $libro;

                if ($book['stock'] > 0 && $book['disponible']) {
                    $extraHtml =
                        '<a class="reserve-btn" href="reservar.php?id=' .
                        intval($book['id']) .
                        '">Reservar</a>';
                } else {
                    $extraHtml = '<div class="reserve-disabled">No disponible</div>';
                }
            ?>
            <?php include __DIR__ . '/components/book_card.php'; ?>
        <?php endforeach; ?>

    </div>
</div>

</body>
</html>
