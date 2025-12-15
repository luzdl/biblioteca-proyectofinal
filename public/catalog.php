<?php
require_once __DIR__ . '/lib/bootstrap.php';

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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
</head>

<body>

    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <?php include __DIR__ . '/components/topbar.php'; ?>

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
                    if (($book['stock'] ?? 0) > 0) {
                        if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'estudiante') {
                            $extraHtml = '<a class="reserve-btn" href="reservar.php?id=' . intval($book['id']) . '">Reservar</a>';
                        } else {
                            $extraHtml = '<button class="reserve-btn needs-login" data-id="' . intval($book['id']) . '">Reservar</button>';
                        }
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
