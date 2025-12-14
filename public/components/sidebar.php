<?php
// Sidebar reusable component - expects to be included from files inside /public
?>
<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="../img/logo_redondo.png" alt="Logo" onerror="this.style.display='none'; this.parentNode.querySelector('.logo-icon').style.display='block'" onload="this.parentNode.querySelector('.logo-icon').style.display='none'">
        <span class="logo-icon material-symbols-outlined" style="display:none" aria-hidden="true">menu_book</span>
        <h2>Biblioteca Digital</h2>
    </div>

    <nav class="sidebar-menu">
        <a href="student_only.php">Catálogo</a>
        <a href="student_reservas.php">Mis reservas</a>
        <a href="student_historial.php">Historial</a>
        <a href="perfil_estudiante.php">Perfil</a>
    </nav>

    <a href="logout.php" class="logout-btn">Cerrar sesión</a>

    <a href="perfil_estudiante.php" class="sidebar-user link-to-profile">
        <img src="../img/user_placeholder.png" alt="Usuario" onerror="this.style.display='none'; this.parentNode.querySelector('.user-icon').style.display='block'" onload="this.parentNode.querySelector('.user-icon').style.display='none'">
        <span class="user-icon material-symbols-outlined" style="display:none" aria-hidden="true">person</span>
        <p><i><?php echo htmlspecialchars($_SESSION['usuario_usuario'] ?? ''); ?></i></p>
    </a>
</aside>
