<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../components/reservas_report.php';

require_role(['administrador', 'bibliotecario']);

$db = (new Database())->getConnection();

reservas_report_handle_export($db);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes | Reservas</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/profile.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/reportes_reservas.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1 class="title">Reportes</h1>

    <?php reservas_report_render($db, ['page_path' => 'app/reportes/reservas.php']); ?>
</main>

</body>
</html>
