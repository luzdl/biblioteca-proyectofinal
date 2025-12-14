<?php
session_start();

require_once __DIR__ . '/../config/router.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'estudiante') {
    redirect('login');
    exit;
}

$db = (new Database())->getConnection();

/*
 * Obtener reservas reales del estudiante logueado
 */
$sql = "SELECT 
            r.id,
            l.titulo,
            l.autor,
            r.estado,
            l.imagen
        FROM reservas r
        JOIN libros l ON l.id = r.libro_id
        WHERE r.usuario_id = :usuario_id
        ORDER BY r.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':usuario_id' => $_SESSION['usuario_id']
]);

$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis reservas</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/student_reservas.css">
    <link rel="stylesheet" href="../css/components/book_card.css">
</head>

<body>

<?php include 'components/sidebar.php'; ?>

<main class="content">

    <h1 class="title">Mis reservas</h1>

    <section class="shelf">
        <h2 class="subtitle">Actualmente reservados</h2>

        <div class="books-row">

            <?php if (empty($reservas)): ?>
                <p class="empty-message">
                    Aún no has reservado ningún libro 📚
                </p>
            <?php else: ?>

                <?php foreach ($reservas as $reserva): ?>
                    <?php
                        $book = [
                            'imagen' => $reserva['imagen'],
                            'titulo' => $reserva['titulo'],
                            'autor'  => $reserva['autor']
                        ];

                        $estadoClase = strtolower(str_replace(' ', '', $reserva['estado']));

                        $extraHtml  = '<p class="estado estado-' . $estadoClase . '">';
                        $extraHtml .= htmlspecialchars($reserva['estado']) . '</p>';

                        if ($reserva['estado'] !== 'cancelada') {
                            $extraHtml .= '<a href="cancelar_reserva.php?id='
                                . intval($reserva['id']) .
                                '" class="cancel-btn">Cancelar reserva</a>';
                        }
                    ?>
                    <?php include __DIR__ . '/components/book_card.php'; ?>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>

        <div class="shelf-line"></div>
    </section>

</main>

</body>
</html>
