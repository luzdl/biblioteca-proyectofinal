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
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1 class="title">Panel de staff</h1>

    <section class="dashboard-cards">
        <a href="<?php echo htmlspecialchars(url_for('app/staff/dashboard.php')); ?>" class="dashboard-card">
            <h3>Panel</h3>
            <p>Resumen general del bibliotecario</p>
        </a>

        <a href="<?php echo htmlspecialchars(url_for('app/staff/libros.php')); ?>" class="dashboard-card">
            <h3>Libros</h3>
            <p>Gestión del catálogo de libros</p>
        </a>

        <a href="<?php echo htmlspecialchars(url_for('app/staff/categorias.php')); ?>" class="dashboard-card">
            <h3>Categorías</h3>
            <p>Administrar categorías de libros</p>
        </a>

        <a href="<?php echo htmlspecialchars(url_for('app/staff/bibliotecario_reservas.php')); ?>" class="dashboard-card">
            <h3>Reservas</h3>
            <p>Gestionar reservas pendientes</p>
        </a>
    </section>
</main>

</body>
</html>
