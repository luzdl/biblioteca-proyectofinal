<?php

$topbarRole = (string)($_SESSION['usuario_rol'] ?? '');
$topbarIsLoggedIn = isset($_SESSION['usuario_usuario']) && (string)$_SESSION['usuario_usuario'] !== '';

$topbarPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
$topbarPath = ltrim($topbarPath, '/');

function _tb_url(string $path): string
{
    if (function_exists('url_for')) {
        return url_for($path);
    }
    return $path;
}

function _tb_active(string $path): bool
{
    $current = (string)($GLOBALS['topbarPath'] ?? '');
    return $current !== '' && str_ends_with($current, ltrim($path, '/'));
}

$topbarLinks = [];

if ($topbarRole === 'administrador') {
    $topbarLinks = [
        ['label' => 'Panel', 'href' => _tb_url('app/admin/index.php')],
        ['label' => 'Usuarios', 'href' => _tb_url('app/admin/usuarios.php')],
        ['label' => 'Roles', 'href' => _tb_url('app/admin/roles.php')],
        ['label' => 'Categorías', 'href' => _tb_url('app/admin/categorias.php')],
        ['label' => 'Carreras', 'href' => _tb_url('app/admin/carreras.php')],
    ];
} elseif ($topbarRole === 'bibliotecario') {
    $topbarLinks = [
        ['label' => 'Panel', 'href' => _tb_url('app/staff/index.php')],
        ['label' => 'Carreras', 'href' => _tb_url('app/admin/carreras.php')],
    ];
} elseif ($topbarRole === 'estudiante') {
    $topbarLinks = [
        ['label' => 'Catálogo', 'href' => _tb_url('app/student/catalog.php')],
        ['label' => 'Mis reservas', 'href' => _tb_url('app/student/reservas.php')],
        ['label' => 'Historial', 'href' => _tb_url('app/student/historial.php')],
    ];
} else {
    $topbarLinks = [
        ['label' => 'Catálogo', 'href' => _tb_url('catalog.php')],
    ];
}

?>
<style>
  :root{
    --tb-height: 72px;
    --tb-bg: rgba(255,255,255,0.86);
    --tb-border: rgba(87,71,55,0.15);
    --tb-text: rgba(87,71,55,0.95);
    --tb-muted: rgba(87,71,55,0.70);
    --tb-accent: var(--sb-accent, #C7764A);
    --text: var(--tb-text);
    --card-bg: #fff;
  }

  body{
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
  }

  body.has-topbar{ padding-top: calc(var(--tb-height) + 16px); }
  body.has-sidebar.has-topbar{ padding-top: calc(var(--tb-height) + 16px); }

  .topbar{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--tb-height);
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--tb-bg);
    border-bottom: 1px solid var(--tb-border);
    backdrop-filter: blur(10px);
    z-index: 60;
    padding: 0 18px;
  }

  body.has-sidebar .topbar{
    padding-left: calc(var(--sb-width-collapsed, 72px) + 32px);
  }

  body.has-sidebar.sidebar-expanded .topbar{
    padding-left: calc(var(--sb-width-expanded, 260px) + 32px);
  }

  @media (max-width: 900px){
    body.has-sidebar .topbar{ padding-left: calc(var(--sb-width-collapsed, 72px) + 32px); }
    body.has-sidebar.sidebar-expanded .topbar{ padding-left: calc(var(--sb-width-expanded, 260px) + 32px); }
  }

  .topbar .tb-left{
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  .topbar .tb-brand{
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--tb-text);
    text-decoration: none;
    font-size: 18px;
    white-space: nowrap;
  }

  .topbar .tb-menu{
    display: flex;
    align-items: center;
    gap: 14px;
    margin-left: 12px;
    flex: 1;
    min-width: 0;
    overflow: hidden;
  }

  .topbar .tb-menu a{
    color: var(--tb-muted);
    text-decoration: none;
    font-weight: 600;
    padding: 8px 10px;
    border-radius: 10px;
    white-space: nowrap;
    transition: background .15s ease, color .15s ease;
  }

  .topbar .tb-menu a:hover{ background: rgba(199,118,74,0.10); color: var(--tb-text); }
  .topbar .tb-menu a.active{ background: rgba(199,118,74,0.14); color: var(--tb-accent); }

  .topbar .tb-right{
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
  }

  .topbar .tb-btn{
    text-decoration: none;
    font-weight: 700;
    padding: 10px 14px;
    border-radius: 999px;
    border: 1px solid var(--tb-border);
    color: var(--tb-text);
    background: rgba(255,255,255,0.55);
    transition: transform .15s ease, background .15s ease;
  }

  .topbar .tb-btn:hover{ transform: translateY(-1px); background: rgba(255,255,255,0.8); }

  .topbar .tb-btn-primary{
    background: rgba(199,118,74,0.18);
    border-color: rgba(199,118,74,0.25);
    color: var(--tb-accent);
  }

  .topbar .tb-icon-btn{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    border: 1px solid var(--tb-border);
    background: rgba(255,255,255,0.55);
    color: var(--tb-text);
    cursor: pointer;
  }

  .topbar .tb-icon-btn .material-symbols-outlined{ font-size: 22px; }

  @media (max-width: 760px){
    .topbar .tb-menu{ display: none; }
  }
</style>

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
<link rel="stylesheet" href="<?php echo htmlspecialchars(_tb_url('css/topbar-dropdown.css')); ?>">

<header class="topbar" role="banner">
  <div class="tb-left">
    <button type="button" class="tb-icon-btn" id="tbToggleSidebar" aria-label="Abrir menú">
      <span class="material-symbols-outlined" aria-hidden="true">menu</span>
    </button>

    <a class="tb-brand" href="<?php echo htmlspecialchars(_tb_url($topbarIsLoggedIn ? 'dashboard.php' : 'catalog.php')); ?>">Biblioteca Digital</a>

    <nav class="tb-menu" aria-label="Navegación superior">
      <?php foreach ($topbarLinks as $link): ?>
        <?php
          $href = (string)($link['href'] ?? '');
          $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? $href);
          $hrefPath = ltrim($hrefPath, '/');
          $isActive = _tb_active($hrefPath);
        ?>
        <a href="<?php echo htmlspecialchars($href); ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
          <?php echo htmlspecialchars($link['label']); ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>

  <div class="tb-right">
    <?php if ($topbarIsLoggedIn): ?>
      <?php include __DIR__ . '/topbar_dropdown.php'; ?>
    <?php else: ?>
      <a class="tb-btn" href="<?php echo htmlspecialchars(_tb_url('login.php')); ?>">Iniciar sesión</a>
      <a class="tb-btn tb-btn-primary" href="<?php echo htmlspecialchars(_tb_url('registro.php')); ?>">Registrarse</a>
    <?php endif; ?>
  </div>
</header>

<script>
  (function(){
    document.body.classList.add('has-topbar');
    var btn = document.getElementById('tbToggleSidebar');
    if (!btn) return;
    btn.addEventListener('click', function(){
      var sb = document.querySelector('.sidebar');
      if (!sb) return;
      sb.classList.toggle('is-open');
    });
  })();
</script>
