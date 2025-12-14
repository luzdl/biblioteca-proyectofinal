<?php
// Sidebar reusable component - expects to be included from files inside /public
// Shows different menu items depending on user role
$_sidebar_rol = $_SESSION['usuario_rol'] ?? '';
$_sidebar_url = function_exists('url_for') ? 'url_for' : null;
function _sb_url($path) {
    global $_sidebar_url;
    return $_sidebar_url ? url_for($path) : $path;
}
?>
<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="<?php echo htmlspecialchars(_sb_url('img/logo_redondo.png')); ?>" alt="Logo" onerror="this.style.display='none'; this.parentNode.querySelector('.logo-icon').style.display='block'" onload="this.parentNode.querySelector('.logo-icon').style.display='none'">
        <span class="logo-icon material-symbols-outlined" style="display:none" aria-hidden="true">menu_book</span>
        <h2>Biblioteca Digital</h2>
    </div>

    <nav class="sidebar-menu">

        <?php if ($_sidebar_rol === 'administrador'): ?>
            <a href="<?php echo htmlspecialchars(_sb_url('app/admin/index.php')); ?>">Panel</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/admin/usuarios.php')); ?>">Usuarios</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/admin/roles.php')); ?>">Roles</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/admin/categorias.php')); ?>">Categorías</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/admin/carreras.php')); ?>">Carreras</a>
        <?php endif; ?>

        <?php if ($_sidebar_rol === 'bibliotecario'): ?>
            <a href="<?php echo htmlspecialchars(_sb_url('app/staff/index.php')); ?>">Panel</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/admin/carreras.php')); ?>">Carreras</a>
        <?php endif; ?>

        <?php if ($_sidebar_rol === 'estudiante'): ?>
            <a href="<?php echo htmlspecialchars(_sb_url('app/student/catalog.php')); ?>">Catálogo</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/student/reservas.php')); ?>">Mis reservas</a>
            <a href="<?php echo htmlspecialchars(_sb_url('app/student/historial.php')); ?>">Historial</a>
        <?php endif; ?>

        <a href="<?php echo htmlspecialchars(_sb_url('app/profile/index.php')); ?>">Perfil</a>
    </nav>

    <a href="<?php echo htmlspecialchars(_sb_url('logout.php')); ?>" class="logout-btn">Cerrar sesión</a>

    <a href="<?php echo htmlspecialchars(_sb_url('app/profile/index.php')); ?>" class="sidebar-user link-to-profile">
        <img src="<?php echo htmlspecialchars(_sb_url('img/user_placeholder.png')); ?>" alt="Usuario" onerror="this.style.display='none'; this.parentNode.querySelector('.user-icon').style.display='block'" onload="this.parentNode.querySelector('.user-icon').style.display='none'">
        <span class="user-icon material-symbols-outlined" style="display:none" aria-hidden="true">person</span>
        <p><i><?php echo htmlspecialchars($_SESSION['usuario_usuario'] ?? ''); ?></i></p>
    </a>
</aside>
