<?php
// components/sidebar.php
// Sidebar colapsable con iconos y labels al hacer hover.
// Asume que se incluye desde /public/*.php

$current  = basename($_SERVER['PHP_SELF'] ?? '');
$username = htmlspecialchars($_SESSION['usuario_usuario'] ?? '');
?>
<style>
  :root{
    --sb-accent: #C7764A;                 /* terracotta de tu paleta */
    --sb-text:   rgba(87,71,55,0.95);
    --sb-icon:   rgba(87,71,55,0.85);
    --sb-muted:  rgba(87,71,55,0.60);
    --sb-bg:     rgba(255,255,255,0.88);
    --sb-border: rgba(87,71,55,0.15);
    --sb-hover:  rgba(199,118,74,0.10);
    --sb-width-collapsed: 72px;
    --sb-width-expanded:  260px;
    --sb-radius: 18px;
  }

  /* ====== Shim de layout: reserva espacio para que navbar y main no se tapen ====== */
  body.has-sidebar{
    /* Reserva ancho máximo para evitar que el contenido “salte” al expandir */
    padding-left: calc(var(--sb-width-expanded) + 16px);
    padding-top: 16px; /* separa del borde superior si tu navbar es sticky */
  }
  @media (max-width: 900px){
    body.has-sidebar{
      padding-left: calc(var(--sb-width-collapsed) + 16px);
    }
  }
  /* Si usas una topbar con esta clase, garantizamos que quede por encima */
  .top-navbar{ z-index: 50; position: sticky; top: 0; }

  /* ====== Sidebar ====== */
  .sidebar{
    position: fixed; left: 16px; top: 16px;
    height: calc(100vh - 32px);
    width: var(--sb-width-collapsed);
    background: var(--sb-bg);
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius);
    backdrop-filter: blur(10px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.08);
    transition: width .25s ease, box-shadow .25s ease;
    overflow: hidden;
    display: flex; flex-direction: column;
    z-index: 20; /* menor que .top-navbar (50) */
  }
  .sidebar:hover,
  .sidebar:focus-within,
  .sidebar.is-open{
    width: var(--sb-width-expanded);
    box-shadow: 0 18px 45px rgba(0,0,0,0.12);
  }

  /* Header/logo */
  .sidebar-logo{
    display:flex; align-items:center; gap:12px;
    padding:14px;
    border-bottom: 1px dashed var(--sb-border);
  }
  .sidebar-logo img{ width:38px; height:38px; object-fit:cover; border-radius:50%; }
  .sidebar-logo .logo-icon{ font-size:28px; color: var(--sb-icon); }
  .sidebar-logo h2{
    font-size:14px; letter-spacing:.18em; text-transform:uppercase;
    color: var(--sb-text); margin:0; white-space:nowrap;
    opacity:0; transform: translateX(-6px);
    transition: opacity .2s ease, transform .2s ease;
  }
  .sidebar:hover .sidebar-logo h2,
  .sidebar:focus-within .sidebar-logo h2{ opacity:1; transform:none; }

  /* Menú */
  .sidebar-menu{ padding: 8px; display:flex; flex-direction:column; gap:6px; }
  .menu-link{
    position:relative;
    display:flex; align-items:center; gap:12px;
    padding:10px 12px;
    border-radius:12px;
    color: var(--sb-text);
    text-decoration:none;
    line-height:1;
    transition: background .15s ease, color .15s ease, transform .1s ease;
  }
  .menu-link .material-symbols-outlined{
    font-size:24px; color: var(--sb-icon); flex: 0 0 24px;
  }
  .menu-label{
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    opacity:0; transform: translateX(-6px);
    transition: opacity .18s ease, transform .18s ease;
    color: var(--sb-text); font-size:14px; letter-spacing:.02em;
  }
  .sidebar:hover .menu-label,
  .sidebar:focus-within .menu-label{ opacity:1; transform:none; }

  .menu-link:hover{ background: var(--sb-hover); }
  .menu-link:active{ transform: translateY(1px); }

  /* Activo */
  .menu-link.active{
    background: linear-gradient(90deg, rgba(199,118,74,.14), rgba(199,118,74,.08));
    color: var(--sb-accent);
  }
  .menu-link.active .material-symbols-outlined{ color: var(--sb-accent); }

  /* Tooltips cuando está colapsado */
  .sidebar:not(:hover):not(:focus-within) .menu-link[data-tooltip]:hover::after{
    content: attr(data-tooltip);
    position: absolute; left: calc(100% + 10px); top: 50%; transform: translateY(-50%);
    background: #fff; color: var(--sb-text);
    border:1px solid var(--sb-border); border-radius:10px;
    padding:6px 10px; font-size:12px; white-space:nowrap;
    box-shadow:0 8px 22px rgba(0,0,0,0.12);
  }

  /* Zona inferior (usuario + logout) */
  .sidebar-bottom{ margin-top:auto; padding:8px; border-top:1px dashed var(--sb-border); }
  .sidebar-user{
    display:flex; align-items:center; gap:12px;
    padding:10px 12px; border-radius:12px; text-decoration:none;
    color: var(--sb-text);
  }
  .sidebar-user img{ width:28px; height:28px; border-radius:50%; object-fit:cover; }
  .sidebar-user .user-icon{ font-size:22px; color: var(--sb-icon); }
  .sidebar-user p{ margin:0; font-size:13px; white-space:nowrap; opacity:0; transform:translateX(-6px); transition:opacity .18s, transform .18s; color: var(--sb-muted); }
  .sidebar:hover .sidebar-user p,
  .sidebar:focus-within .sidebar-user p{ opacity:1; transform:none; }

  .logout-btn{
    display:flex; align-items:center; gap:12px;
    margin-top:8px; padding:10px 12px; border-radius:12px;
    text-decoration:none; color: var(--sb-text);
  }
  .logout-btn .material-symbols-outlined{ font-size:22px; color: var(--sb-icon); }
  .logout-btn:hover{ background: var(--sb-hover); }

  /* Accesibilidad: reducir movimiento */
  @media (prefers-reduced-motion: reduce){
    .sidebar, .menu-label, .sidebar-logo h2{ transition: none; }
  }
</style>

<aside class="sidebar" aria-label="Navegación principal">
  <div class="sidebar-logo">
    <img src="../img/logo_redondo.png"
         alt="Logo"
         onerror="this.style.display='none'; this.parentNode.querySelector('.logo-icon').style.display='block'"
         onload="this.parentNode.querySelector('.logo-icon').style.display='none'">
    <span class="logo-icon material-symbols-outlined" style="display:none" aria-hidden="true">menu_book</span>
    <h2>Biblioteca Digital</h2>
  </div>

  <nav class="sidebar-menu" role="navigation">
    <a class="menu-link <?php echo $current==='student_only.php'?'active':''; ?>"
       href="student_only.php"
       data-tooltip="Catálogo" aria-label="Catálogo">
      <span class="material-symbols-outlined" aria-hidden="true">local_library</span>
      <span class="menu-label">Catálogo</span>
    </a>

    <a class="menu-link <?php echo $current==='student_reservas.php'?'active':''; ?>"
       href="student_reservas.php"
       data-tooltip="Mis reservas" aria-label="Mis reservas">
      <span class="material-symbols-outlined" aria-hidden="true">bookmarks</span>
      <span class="menu-label">Mis reservas</span>
    </a>

    <a class="menu-link <?php echo $current==='student_historial.php'?'active':''; ?>"
       href="student_historial.php"
       data-tooltip="Historial" aria-label="Historial">
      <span class="material-symbols-outlined" aria-hidden="true">history</span>
      <span class="menu-label">Historial</span>
    </a>

    <a class="menu-link <?php echo $current==='perfil_estudiante.php'?'active':''; ?>"
       href="perfil_estudiante.php"
       data-tooltip="Perfil" aria-label="Perfil">
      <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
      <span class="menu-label">Perfil</span>
    </a>
  </nav>

  <div class="sidebar-bottom">
    <a href="perfil_estudiante.php" class="sidebar-user link-to-profile" aria-label="Ir al perfil">
      <img src="../img/user_placeholder.png" alt="Usuario"
           onerror="this.style.display='none'; this.parentNode.querySelector('.user-icon').style.display='block'"
           onload="this.parentNode.querySelector('.user-icon').style.display='none'">
      <span class="user-icon material-symbols-outlined" style="display:none" aria-hidden="true">person</span>
      <p><i><?php echo $username; ?></i></p>
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

    <a href="logout.php" class="logout-btn" aria-label="Cerrar sesión">
      <span class="material-symbols-outlined" aria-hidden="true">logout</span>
      <span class="menu-label">Cerrar sesión</span>
    </a>
  </div>
</aside>

<script>
  // Añade una clase al body para reservar espacio lateral del sidebar.
  (function(){
    document.documentElement.classList.add('js');
    document.body.classList.add('has-sidebar');
  })();
</script>
