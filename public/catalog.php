<?php
session_start();

/* Libros simulados mientras no haya base de datos */
$libros = [
    ["id"=>1,"titulo"=>"Cien años de soledad","autor"=>"Gabriel García Márquez","categoria"=>"Novela","stock"=>4,"imagen"=>"../img/libro1.jpg"],
    ["id"=>2,"titulo"=>"1984","autor"=>"George Orwell","categoria"=>"Distopía","stock"=>0,"imagen"=>"../img/libro2.jpg"],
    ["id"=>3,"titulo"=>"El principito","autor"=>"Antoine de Saint-Exupéry","categoria"=>"Fábula","stock"=>7,"imagen"=>"../img/libro3.jpg"],
    ["id"=>4,"titulo"=>"Don Quijote de la Mancha","autor"=>"Miguel de Cervantes","categoria"=>"Clásico","stock"=>2,"imagen"=>"../img/libro4.jpg"],
];

$busqueda = $_GET["q"] ?? "";
if ($busqueda !== "") {
    $libros = array_filter($libros, function($libro) use ($busqueda) {
        $q = strtolower($busqueda);
        return str_contains(strtolower($libro["titulo"]), $q)
            || str_contains(strtolower($libro["autor"]), $q)
            || str_contains(strtolower($libro["categoria"]), $q);
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca | Catálogo</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- Estilos existentes -->
    <link rel="stylesheet" href="../css/student.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/components/book_card.css">
    <link rel="stylesheet" href="../css/topbar-dropdown.css">

    <!-- Iconos para el sidebar -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <!-- Ajustes mínimos para convivir con el sidebar -->
    <style>
      body.has-sidebar { padding-left: 276px; padding-top: 16px; } /* 260 + margen */
      @media (max-width: 900px){ body.has-sidebar { padding-left: 88px; } }
      .topbar{ position: sticky; top: 0; z-index: 50; background:#fff;
               border-bottom:1px solid rgba(87,71,55,.15); }
      .container{ padding-top: 16px; }
    </style>
</head>

<body class="has-sidebar">

    <!-- ✅ RUTA CORRECTA DEL SIDEBAR -->
    include __DIR__ . '/components/sidebar.php';


    <!-- Barra superior -->
    <header class="topbar">
        <div class="logo">Biblioteca Digital</div>

        <nav class="menu">
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante'): ?>
                <a href="student_only.php" class="active">Catálogo</a>
                <a href="student_reservas.php">Mis reservas</a>
                <a href="student_historial.php">Historial</a>
            <?php else: ?>
                <a href="catalog.php" class="active">Catálogo</a>
                <a href="login.php" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </nav>

        <?php include __DIR__ . '/components/topbar_dropdown.php'; ?>
    </header>

    <div class="container">
        <!-- Buscador -->
        <form method="GET" class="search-box">
            <input type="text" name="q"
                   placeholder="Buscar por título, autor o categoría..."
                   value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit">Buscar</button>
        </form>

        <h2 class="section-title">Libros disponibles</h2>

        <div class="book-grid">
            <?php if (empty($libros)): ?>
                <p>No se encontraron libros para la búsqueda seleccionada.</p>
            <?php endif; ?>

            <?php foreach ($libros as $libro): ?>
                <?php
                    $book = $libro;
                    if ($book['stock'] > 0) {
                        if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante') {
                            $extraHtml = '<a class="reserve-btn" href="reservar.php?id=' . intval($book['id']) . '">Reservar</a>';
                        } else {
                            $extraHtml = '<button class="reserve-btn needs-login" data-id="' . intval($book['id']) . '">Reservar</button>';
                        }
<body>

<!-- Barra superior -->
<header class="topbar">
    <div class="logo">Biblioteca Digital</div>

    <nav class="menu">
        <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante'): ?>
            <a href="student_only.php" class="active">Catálogo</a>
            <a href="student_reservas.php">Mis reservas</a>
            <a href="student_historial.php">Historial</a>
        <?php else: ?>
            <a href="catalog.php" class="active">Catálogo</a>
            <a href="login.php" class="login-btn">Iniciar sesión</a>
        <?php endif; ?>
    </nav>

    <?php include __DIR__ . '/components/topbar_dropdown.php'; ?>
</header>


<div class="container">

    <!-- Buscador -->
    <form method="GET" class="search-box">
        <input 
            type="text" 
            name="q" 
            placeholder="Buscar por título, autor o categoría..." 
            value="<?php echo htmlspecialchars($busqueda); ?>"
        >
        <button type="submit">Buscar</button>
    </form>

    <h2 class="section-title">Libros disponibles</h2>

    <!-- Grid de libros -->
    <div class="book-grid">

        <?php if (empty($libros)): ?>
            <p>No se encontraron libros para la búsqueda seleccionada.</p>
        <?php endif; ?>

        <?php foreach ($libros as $libro): ?>
            <?php
                $book = $libro;
                if ($book['stock'] > 0) {
                    if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante') {
                        $extraHtml = '<a class="reserve-btn" href="reservar.php?id=' . intval($book['id']) . '">Reservar</a>';
                    } else {
                        $extraHtml = '<div class="reserve-disabled">No disponible</div>';
                    }
                ?>
                <?php include __DIR__ . '/components/book_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include __DIR__ . '/components/modal.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.needs-login').forEach(function(btn){
            btn.addEventListener('click', function(){
                var id = this.getAttribute('data-id');
                var next = encodeURIComponent('reservar.php?id=' + id);
                showAppModal({
                    title: 'Inicia sesión para reservar',
                    body: 'Debes iniciar sesión para poder realizar la reserva. ¿Deseas ir a iniciar sesión o registrarte ahora?',
                    confirmText: 'Ir a iniciar sesión',
                    onConfirm: function(){ window.location.href = 'login.php?next=' + next; }
                });
            });
        });
    });
    </script>

</body>
</html>
