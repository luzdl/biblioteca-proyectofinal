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
            <a href="perfil_estudiante.php">Perfil</a>
            <a href="logout.php" class="logout">Cerrar sesión</a>
        </div>
    </details>
</div>
<?php endif;
