<aside class="sidebar">
    <div class="sidebar-menu">
        <a href="bibliotecario_reservas.php">📚 Reservas</a>
        <a href="bibliotecario_libros.php">📖 Libros</a>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>

    <div class="sidebar-user">
        <p><i><?= htmlspecialchars($_SESSION['usuario_usuario']); ?></i></p>
    </div>
</aside>
