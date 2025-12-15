<?php

$sidebarRole = (string)($_SESSION['usuario_rol'] ?? '');
$isLoggedIn = isset($_SESSION['usuario_usuario']) && (string)$_SESSION['usuario_usuario'] !== '';
$username = htmlspecialchars($_SESSION['usuario_usuario'] ?? '');
$currentPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
$currentPath = ltrim($currentPath, '/');

function _sb_url(string $path): string
{
    if (function_exists('url_for')) {
        return url_for($path);
    }
    return $path;
}

function _sb_active(string $path): bool
{
    $current = (string)($GLOBALS['currentPath'] ?? '');
    return $current !== '' && str_ends_with($current, ltrim($path, '/'));
}

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

  body{
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
  }

  /* ====== Shim de layout: reserva espacio para que navbar y main no se tapen ====== */
  body.has-sidebar{
    padding-left: calc(var(--sb-width-collapsed) + 16px);
  }
  body.has-sidebar.sidebar-expanded{
    padding-left: calc(var(--sb-width-expanded) + 16px);
  }
  @media (max-width: 900px){
    body.has-sidebar{
      padding-left: calc(var(--sb-width-collapsed) + 16px);
    }
    body.has-sidebar.sidebar-expanded{
      padding-left: calc(var(--sb-width-expanded) + 16px);
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

  body.has-topbar .sidebar{
    top: calc(var(--tb-height, 72px) + 16px);
    height: calc(100vh - var(--tb-height, 72px) - 32px);
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

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

<aside class="sidebar" aria-label="Navegación principal">
  <div class="sidebar-logo">
    <img src="<?php echo htmlspecialchars(_sb_url('img/logo_redondo.png')); ?>"
         alt="Logo"
         onerror="this.style.display='none'; this.parentNode.querySelector('.logo-icon').style.display='block'"
         onload="this.parentNode.querySelector('.logo-icon').style.display='none'">
    <span class="logo-icon material-symbols-outlined" style="display:none" aria-hidden="true">menu_book</span>
    <h2>Biblioteca Digital</h2>
  </div>

  <nav class="sidebar-menu" role="navigation">

    <?php if (!$isLoggedIn): ?>
      <a class="menu-link <?php echo _sb_active('public/catalog.php') || _sb_active('catalog.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('catalog.php')); ?>"
         data-tooltip="Catálogo" aria-label="Catálogo">
        <span class="material-symbols-outlined" aria-hidden="true">local_library</span>
        <span class="menu-label">Catálogo</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/login.php') || _sb_active('login.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('login.php')); ?>"
         data-tooltip="Iniciar sesión" aria-label="Iniciar sesión">
        <span class="material-symbols-outlined" aria-hidden="true">login</span>
        <span class="menu-label">Iniciar sesión</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/registro.php') || _sb_active('registro.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('registro.php')); ?>"
         data-tooltip="Registrarse" aria-label="Registrarse">
        <span class="material-symbols-outlined" aria-hidden="true">person_add</span>
        <span class="menu-label">Registrarse</span>
      </a>
    <?php endif; ?>

    <?php if ($isLoggedIn && $sidebarRole === 'administrador'): ?>
      <a class="menu-link <?php echo _sb_active('public/app/admin/index.php') || _sb_active('app/admin/index.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/admin/index.php')); ?>"
         data-tooltip="Panel" aria-label="Panel">
        <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
        <span class="menu-label">Panel</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/admin/usuarios.php') || _sb_active('app/admin/usuarios.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/admin/usuarios.php')); ?>"
         data-tooltip="Usuarios" aria-label="Usuarios">
        <span class="material-symbols-outlined" aria-hidden="true">group</span>
        <span class="menu-label">Usuarios</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/admin/roles.php') || _sb_active('app/admin/roles.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/admin/roles.php')); ?>"
         data-tooltip="Roles" aria-label="Roles">
        <span class="material-symbols-outlined" aria-hidden="true">admin_panel_settings</span>
        <span class="menu-label">Roles</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/admin/categorias.php') || _sb_active('app/admin/categorias.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/admin/categorias.php')); ?>"
         data-tooltip="Categorías" aria-label="Categorías">
        <span class="material-symbols-outlined" aria-hidden="true">category</span>
        <span class="menu-label">Categorías</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/admin/carreras.php') || _sb_active('app/admin/carreras.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/admin/carreras.php')); ?>"
         data-tooltip="Carreras" aria-label="Carreras">
        <span class="material-symbols-outlined" aria-hidden="true">school</span>
        <span class="menu-label">Carreras</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/reportes/reservas.php') || _sb_active('app/reportes/reservas.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/reportes/reservas.php')); ?>"
         data-tooltip="Reportes" aria-label="Reportes">
        <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span>
        <span class="menu-label">Reportes</span>
      </a>
    <?php endif; ?>

    <?php if ($isLoggedIn && $sidebarRole === 'bibliotecario'): ?>
      <a class="menu-link <?php echo _sb_active('public/app/staff/dashboard.php') || _sb_active('app/staff/dashboard.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/staff/dashboard.php')); ?>"
         data-tooltip="Panel" aria-label="Panel">
        <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
        <span class="menu-label">Panel</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/staff/libros.php') || _sb_active('app/staff/libros.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/staff/libros.php')); ?>"
         data-tooltip="Libros" aria-label="Libros">
        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
        <span class="menu-label">Libros</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/staff/categorias.php') || _sb_active('app/staff/categorias.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/staff/categorias.php')); ?>"
         data-tooltip="Categorías" aria-label="Categorías">
        <span class="material-symbols-outlined" aria-hidden="true">category</span>
        <span class="menu-label">Categorías</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/staff/bibliotecario_reservas.php') || _sb_active('app/staff/bibliotecario_reservas.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/staff/bibliotecario_reservas.php')); ?>"
         data-tooltip="Reservas" aria-label="Reservas">
        <span class="material-symbols-outlined" aria-hidden="true">list_alt</span>
        <span class="menu-label">Reservas</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/reportes/reservas.php') || _sb_active('app/reportes/reservas.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/reportes/reservas.php')); ?>"
         data-tooltip="Reportes" aria-label="Reportes">
        <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span>
        <span class="menu-label">Reportes</span>
      </a>
    <?php endif; ?>

    <?php if ($isLoggedIn && $sidebarRole === 'estudiante'): ?>
      <a class="menu-link <?php echo _sb_active('public/app/student/catalog.php') || _sb_active('app/student/catalog.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/student/catalog.php')); ?>"
         data-tooltip="Catálogo" aria-label="Catálogo">
        <span class="material-symbols-outlined" aria-hidden="true">local_library</span>
        <span class="menu-label">Catálogo</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/student/reservas.php') || _sb_active('app/student/reservas.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/student/reservas.php')); ?>"
         data-tooltip="Mis reservas" aria-label="Mis reservas">
        <span class="material-symbols-outlined" aria-hidden="true">bookmarks</span>
        <span class="menu-label">Mis reservas</span>
      </a>

      <a class="menu-link <?php echo _sb_active('public/app/student/historial.php') || _sb_active('app/student/historial.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/student/historial.php')); ?>"
         data-tooltip="Historial" aria-label="Historial">
        <span class="material-symbols-outlined" aria-hidden="true">history</span>
        <span class="menu-label">Historial</span>
      </a>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
      <a class="menu-link <?php echo _sb_active('public/app/profile/index.php') || _sb_active('app/profile/index.php') ? 'active' : ''; ?>"
         href="<?php echo htmlspecialchars(_sb_url('app/profile/index.php')); ?>"
         data-tooltip="Perfil" aria-label="Perfil">
        <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
        <span class="menu-label">Perfil</span>
      </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-bottom">
    <?php if ($isLoggedIn): ?>
      <a href="<?php echo htmlspecialchars(_sb_url('app/profile/index.php')); ?>" class="sidebar-user link-to-profile" aria-label="Ir al perfil">
        <img src="<?php echo htmlspecialchars(_sb_url('img/user_placeholder.png')); ?>" alt="Usuario"
             onerror="this.style.display='none'; this.parentNode.querySelector('.user-icon').style.display='block'"
             onload="this.parentNode.querySelector('.user-icon').style.display='none'">
        <span class="user-icon material-symbols-outlined" style="display:none" aria-hidden="true">person</span>
        <p><i><?php echo $username; ?></i></p>
      </a>

      <a href="<?php echo htmlspecialchars(_sb_url('logout.php')); ?>" class="logout-btn" aria-label="Cerrar sesión">
        <span class="material-symbols-outlined" aria-hidden="true">logout</span>
        <span class="menu-label">Cerrar sesión</span>
      </a>
    <?php endif; ?>
  </div>
</aside>

<script>
  // Añade una clase al body para reservar espacio lateral del sidebar.
  (function(){
    document.documentElement.classList.add('js');
    document.body.classList.add('has-sidebar');

    var sb = document.querySelector('.sidebar');
    if (!sb) return;

    var hovering = false;
    function syncExpanded(){
      var expanded = hovering || sb.classList.contains('is-open');
      document.body.classList.toggle('sidebar-expanded', expanded);
    }

    sb.addEventListener('mouseenter', function(){ hovering = true; syncExpanded(); });
    sb.addEventListener('mouseleave', function(){ hovering = false; syncExpanded(); });
    sb.addEventListener('focusin', function(){ hovering = true; syncExpanded(); });
    sb.addEventListener('focusout', function(){ hovering = false; syncExpanded(); });

    var obs = new MutationObserver(function(){ syncExpanded(); });
    obs.observe(sb, { attributes: true, attributeFilter: ['class'] });
    syncExpanded();
  })();
</script>
