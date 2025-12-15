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

/* Distribución simple en 3 repisas */
$chunks = array_chunk(array_values($libros), max(1, ceil((count($libros) ?: 1)/3)));
$shelves = [
    "En lectura"  => $chunks[0] ?? [],
    "Por leer"    => $chunks[1] ?? [],
    "Finalizados" => $chunks[2] ?? [],
];
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
    <!-- NUEVO: estilos de repisas -->
    <link rel="stylesheet" href="../css/catalog.css">

    <!-- Iconos para el sidebar -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <!-- Compensación del sidebar (ajustada por breakpoints también en catalog.css) -->
    <style>
      /* Desktop amplio */
      @media (min-width: 1201px){
        body.has-sidebar { padding-left: 276px; padding-top: 16px; }
      }
      /* Tablet / desktop angosto: sidebar más cerca */
      @media (min-width: 901px) and (max-width:1200px){
        body.has-sidebar { padding-left: 240px; padding-top: 16px; }
      }
      /* Móvil / sidebar colapsado */
      @media (max-width: 900px){
        body.has-sidebar { padding-left: 88px; padding-top: 12px; }
      }

      .topbar{ position: sticky; top: 0; z-index: 50; background:#fff;
               border-bottom:1px solid rgba(87,71,55,.15); }
    </style>
</head>

<body class="has-sidebar">

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <!-- Barra superior -->
    <header class="topbar">
        <div class="logo">Biblioteca Digital</div>

        <nav class="menu">
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante'): ?>
                <a href="<?php echo htmlspecialchars(function_exists('url_for') ? url_for('app/student/catalog.php') : 'app/student/catalog.php'); ?>" class="active">Catálogo</a>
                <a href="<?php echo htmlspecialchars(function_exists('url_for') ? url_for('app/student/reservas.php') : 'app/student/reservas.php'); ?>">Mis reservas</a>
                <a href="<?php echo htmlspecialchars(function_exists('url_for') ? url_for('app/student/historial.php') : 'app/student/historial.php'); ?>">Historial</a>
            <?php else: ?>
                <a href="catalog.php" class="active">Catálogo</a>
                <a href="login.php" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </nav>

        <?php include __DIR__ . '/components/topbar_dropdown.php'; ?>
    </header>

    <!-- WRAPPER con padding lateral propio para que nada quede detrás del sidebar -->
    <div class="catalog-wrap">
        <!-- Buscador -->
        <form method="GET" class="search-box">
            <input type="text" name="q"
                   placeholder="Buscar por título, autor o categoría..."
                   value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit">Buscar</button>
        </form>

        <h2 class="section-title">Mi biblioteca</h2>

        <!-- ================== REPISAS ================== -->
        <div class="shelves">
            <?php if (empty($libros)): ?>
                <p>No se encontraron libros para la búsqueda seleccionada.</p>
            <?php endif; ?>

            <?php foreach ($shelves as $shelfTitle => $books): ?>
            <section class="shelf-block">
                <div class="shelf-header">
                    <h3><?php echo htmlspecialchars($shelfTitle); ?></h3>
                    <a class="shelf-link" href="#">Ver todo</a>
                </div>

                <div class="shelf-row">
                    <?php foreach ($books as $book): ?>
                        <article class="book-item">
                            <div class="book-cover">
                                <img src="<?php echo htmlspecialchars($book['imagen']); ?>"
                                     alt="Portada de <?php echo htmlspecialchars($book['titulo']); ?>">
                            </div>
                            <div class="book-stand"></div>

                            <div class="book-hover">
                                <div class="meta">
                                    <strong><?php echo htmlspecialchars($book['titulo']); ?></strong>
                                    <span><?php echo htmlspecialchars($book['autor']); ?></span>
                                </div>
                                <?php if (($book['stock'] ?? 0) > 0): ?>
                                  <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante'): ?>
                                    <a class="btn-reserve" href="reservar.php?id=<?php echo intval($book['id']); ?>">Reservar</a>
                                  <?php else: ?>
                                    <button class="btn-reserve needs-login" data-id="<?php echo intval($book['id']); ?>">Reservar</button>
                                  <?php endif; ?>
                                <?php else: ?>
                                    <div class="reserve-disabled">No disponible</div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Madera de la repisa -->
                <div class="wood-shelf"></div>
            </section>
            <?php endforeach; ?>
        </div>
        <!-- ============================================= -->
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
