<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

$libros = [
    [
        'id' => 1,
        'titulo' => 'Cien años de soledad',
        'autor' => 'Gabriel García Márquez',
        'categoria' => 'Novela',
        'stock' => 4,
        'imagen' => url_for('img/libro1.jpg'),
    ],
    [
        'id' => 2,
        'titulo' => '1984',
        'autor' => 'George Orwell',
        'categoria' => 'Distopía',
        'stock' => 0,
        'imagen' => url_for('img/libro2.jpg'),
    ],
    [
        'id' => 3,
        'titulo' => 'El principito',
        'autor' => 'Antoine de Saint-Exupéry',
        'categoria' => 'Fábula',
        'stock' => 7,
        'imagen' => url_for('img/libro3.jpg'),
    ],
    [
        'id' => 4,
        'titulo' => 'Don Quijote de la Mancha',
        'autor' => 'Miguel de Cervantes',
        'categoria' => 'Clásico',
        'stock' => 2,
        'imagen' => url_for('img/libro4.jpg'),
    ],
];

$busqueda = $_GET['q'] ?? '';
if ($busqueda !== '') {
    $libros = array_filter($libros, function ($libro) use ($busqueda) {
        $q = strtolower($busqueda);
        return str_contains(strtolower($libro['titulo']), $q)
            || str_contains(strtolower($libro['autor']), $q)
            || str_contains(strtolower($libro['categoria']), $q);
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca | Estudiante</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/topbar-dropdown.css')); ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<div class="container">

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

    <div class="book-grid">

        <?php if (empty($libros)): ?>
            <p>No se encontraron libros para la búsqueda seleccionada.</p>
        <?php endif; ?>

        <?php foreach ($libros as $libro): ?>
            <?php
                $book = $libro;
                if (($book['stock'] ?? 0) > 0) {
                    $extraHtml = '<a class="reserve-btn" href="' . htmlspecialchars(url_for('reservar.php', ['id' => intval($book['id'])])) . '">Reservar</a>';
                } else {
                    $extraHtml = '<div class="reserve-disabled">No disponible</div>';
                }
            ?>
            <?php include __DIR__ . '/../../components/book_card.php'; ?>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>
