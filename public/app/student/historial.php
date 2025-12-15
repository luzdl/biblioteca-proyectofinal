<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

$historial = [
    [
        'titulo' => 'Cien años de soledad',
        'autor' => 'García Márquez',
        'fecha' => '2024-01-12',
        'imagen' => url_for('img/libro1.jpg'),
    ],
    [
        'titulo' => 'El Señor de los Anillos',
        'autor' => 'J.R.R. Tolkien',
        'fecha' => '2024-02-01',
        'imagen' => url_for('img/libro_lotr.jpg'),
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_reservas.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">

    <h1 class="title">Historial</h1>
    <h2 class="subtitle">Libros reservados anteriormente</h2>

    <div class="books-row">

        <?php foreach ($historial as $h): ?>
            <?php
                $book = [
                    'imagen' => $h['imagen'],
                    'titulo' => $h['titulo'],
                    'autor'  => $h['autor'],
                ];
                $extraHtml = '<p class="estado">Fecha: ' . htmlspecialchars($h['fecha']) . '</p>';
            ?>
            <?php include __DIR__ . '/../../components/book_card.php'; ?>
        <?php endforeach; ?>

    </div>

    <div class="shelf-line"></div>

</main>

</body>
</html>
