<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_usuario']) && $_SESSION['usuario_usuario'] !== ''): ?>
<div class="usuario">
    <details class="dropdown" role="list">
        <summary class="username-summary" role="button">
            <?php echo htmlspecialchars($_SESSION['usuario_usuario']); ?> <span class="caret">▾</span>
        </summary>
        <div class="dropdown-menu">
            <a href="<?php echo htmlspecialchars(function_exists('url_for') ? url_for('app/profile/index.php') : 'app/profile/index.php'); ?>">Perfil</a>
            <a href="<?php echo htmlspecialchars(function_exists('url_for') ? url_for('logout.php') : 'logout.php'); ?>" class="logout">Cerrar sesión</a>
        </div>
    </details>
</div>
<?php endif;
