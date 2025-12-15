<aside class="sidebar">

    <!-- NOMBRE DEL SISTEMA -->
    <div class="sidebar-header">
        <h2>ReadOwl</h2>
        <p class="role-tag">Bibliotecario</p>
    </div>

    <!-- MENÚ DEL BIBLIOTECARIO -->
    <nav class="sidebar-menu">

        <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">
            <span class="material-symbols-outlined">monitoring</span>
            Panel general
        </a>

        <a href="libros.php" class="<?= $active === 'libros' ? 'active' : '' ?>">
            <span class="material-symbols-outlined">menu_book</span>
            Gestión de libros
        </a>

        <a href="categorias.php" class="<?= $active === 'categorias' ? 'active' : '' ?>">
            <span class="material-symbols-outlined">category</span>
            Categorías
        </a>

        <a href="reservas.php" class="<?= $active === 'reservas' ? 'active' : '' ?>">
            <span class="material-symbols-outlined">list_alt</span>
            Reservas
        </a>

        <a href="reportes.php" class="<?= $active === 'reportes' ? 'active' : '' ?>">
            <span class="material-symbols-outlined">bar_chart</span>
            Reportes
        </a>

    </nav>

    <!-- CERRAR SESIÓN -->
    <a class="logout-btn" href="../public/logout.php">
        <span class="material-symbols-outlined">logout</span>
        Cerrar sesión
    </a>

    <!-- USUARIO -->
    <div class="sidebar-user">
        <span class="material-symbols-outlined user-icon">account_circle</span>
        <p><i><?= htmlspecialchars($_SESSION['usuario_usuario']); ?></i></p>
    </div>

</aside>
