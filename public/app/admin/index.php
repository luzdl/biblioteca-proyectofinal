<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrador | Biblioteca Digital</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/admin.css')); ?>">
</head>
<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1 class="title">Panel de administrador</h1>

    <section class="dashboard-cards">
        <a href="<?php echo htmlspecialchars(url_for('app/admin/usuarios.php')); ?>" class="dashboard-card">
            <h3>Usuarios</h3>
            <p>Gestionar usuarios del sistema</p>
        </a>
        <a href="<?php echo htmlspecialchars(url_for('app/admin/roles.php')); ?>" class="dashboard-card">
            <h3>Roles</h3>
            <p>Gestionar roles y permisos</p>
        </a>
        <a href="<?php echo htmlspecialchars(url_for('app/admin/categorias.php')); ?>" class="dashboard-card">
            <h3>Categorías</h3>
            <p>Gestionar categorías de libros</p>
        </a>
        <a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>" class="dashboard-card">
            <h3>Carreras</h3>
            <p>Gestionar carreras universitarias</p>
        </a>
    </section>
</main>

</body>
</html>
