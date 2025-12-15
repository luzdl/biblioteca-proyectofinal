<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_role(['estudiante']);

$db = (new Database())->getConnection();

// Galería: todos los libros
$galeriaLibros = [];
try {
    $query = "
        SELECT
            l.id,
            l.titulo,
            l.autor,
            l.stock,
            l.portada AS imagen,
            c.nombre AS categoria
        FROM libros l
        LEFT JOIN categorias_libros c ON c.id = l.categoria_id
        ORDER BY l.titulo
    ";

    $stmt = $db->query($query);
    $galeriaLibros = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($galeriaLibros as &$libro) {
        if (!empty($libro['imagen'])) {
            $libro['imagen'] = url_for('img/portadas/' . ltrim((string)$libro['imagen'], '/'));
        } else {
            $libro['imagen'] = url_for('img/default-book.png');
        }
    }
    unset($libro);
} catch (Exception $e) {
    $galeriaLibros = [];
}

$busqueda = $_GET["q"] ?? "";
if ($busqueda !== "") {
    $q = strtolower($busqueda);
    $galeriaLibros = array_values(array_filter($galeriaLibros, function($libro) use ($q) {
        return str_contains(strtolower($libro["titulo"] ?? ''), $q)
            || str_contains(strtolower($libro["autor"] ?? ''), $q)
            || str_contains(strtolower($libro["categoria"] ?? ''), $q);
    }));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca | Catálogo</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/components/book_card.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/catalog.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/libros_mas_usados.css')); ?>">
</head>

<body>

    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <?php include __DIR__ . '/../../components/topbar.php'; ?>
    <?php include_once __DIR__ . '/../../components/libros_mas_usados.php'; ?>

    <div class="catalog-wrap">
        <!-- Buscador -->
        <form method="GET" class="search-box">
            <input type="text" name="q"
                   placeholder="Buscar por título, autor o categoría..."
                   value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit">Buscar</button>
        </form>

        <?php if (function_exists('libros_mas_usados_render')): ?>
            <?php libros_mas_usados_render($db, ['preserve_params' => ['q'], 'limit' => 5]); ?>
        <?php endif; ?>

        <h2 class="section-title">Galería</h2>

        <div class="shelves">
            <?php if (empty($galeriaLibros)): ?>
                <p>No se encontraron libros para la búsqueda seleccionada.</p>
            <?php else: ?>
                <section class="shelf-block">
                    <div class="shelf-header">
                        <h3>Libros</h3>
                        <a class="shelf-link" href="#">Ver todo</a>
                    </div>

                    <div class="shelf-row">
                        <?php foreach ($galeriaLibros as $book): ?>
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
                                        <a class="btn-reserve" href="<?php echo htmlspecialchars(url_for('reservar', ['id' => intval($book['id'])])); ?>">Reservar</a>
                                    <?php else: ?>
                                        <div class="reserve-disabled">No disponible</div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="wood-shelf"></div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('a.btn-reserve').forEach(function(a){
            a.addEventListener('click', function(e){
                e.preventDefault();
                var href = this.getAttribute('href');
                if (!href) {
                    return;
                }

                Swal.fire({
                    title: '¿Está seguro que quiere reservar este libro?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, reservar',
                    cancelButtonText: 'No'
                }).then(function(result){
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });

        document.querySelectorAll('.needs-login').forEach(function(btn){
            btn.addEventListener('click', function(){
                var id = this.getAttribute('data-id');
                var next = encodeURIComponent('reservar.php?id=' + id);
                showAppModal({
                    title: 'Inicia sesión para reservar',
                    body: 'Debes iniciar sesión para poder realizar la reserva. ¿Deseas ir a iniciar sesión o registrarte ahora?',
                    confirmText: 'Ir a iniciar sesión',
                    onConfirm: function(){ window.location.href = '../../login.php?next=' + next; }
                });
            });
        });
    });
    </script>

</body>
</html>
