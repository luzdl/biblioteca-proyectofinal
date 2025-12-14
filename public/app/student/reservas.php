<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

$reservas = [
    [
        'id' => 1,
        'titulo' => 'Cien años de soledad',
        'autor' => 'García Márquez',
        'estado' => 'En curso',
        'imagen' => url_for('img/libro1.jpg'),
    ],
    [
        'id' => 2,
        'titulo' => 'El Señor de los Anillos',
        'autor' => 'J.R.R. Tolkien',
        'estado' => 'Pendiente',
        'imagen' => url_for('img/libro_lotr.jpg'),
    ],
    [
        'id' => 3,
        'titulo' => '1984',
        'autor' => 'George Orwell',
        'estado' => 'Finalizado',
        'imagen' => url_for('img/libro_1984.jpg'),
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis reservas</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_reservas.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>

<main class="content">

    <h1 class="title">Mis reservas</h1>

    <section class="shelf">
        <h2 class="subtitle">Actualmente reservados</h2>

        <div class="books-row">

            <?php foreach ($reservas as $reserva): ?>
                <?php
                    $book = [
                        'imagen' => $reserva['imagen'],
                        'titulo' => $reserva['titulo'],
                        'autor'  => $reserva['autor'],
                    ];
                    $estadoClass = strtolower(str_replace(' ', '', $reserva['estado']));
                    $extraHtml = '<p class="estado estado-' . $estadoClass . '">' . htmlspecialchars($reserva['estado']) . '</p>';
                    $extraHtml .= '<a href="#" class="cancel-btn">Cancelar reserva</a>';
                ?>
                <?php include __DIR__ . '/../../components/book_card.php'; ?>
            <?php endforeach; ?>

        </div>

        <div class="shelf-line"></div>
    </section>

</main>

</body>
</html>
