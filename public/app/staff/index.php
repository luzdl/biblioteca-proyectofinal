<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador', 'bibliotecario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Staff | Biblioteca Digital</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>

<main class="content">
    <h1 class="title">Panel de staff</h1>

    <section class="dashboard-cards">
        <a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>" class="dashboard-card">
            <h3>Carreras</h3>
            <p>Gestionar carreras universitarias</p>
        </a>
    </section>
</main>

</body>
</html>
