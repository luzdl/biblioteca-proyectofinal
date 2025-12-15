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

/* Búsqueda simple (mb_strtolower para soporte UTF-8/acentos) */
$busqueda = $_GET["q"] ?? "";
if ($busqueda !== "") {
    $q = mb_strtolower($busqueda, 'UTF-8');
    $galeriaLibros = array_values(array_filter($galeriaLibros, function($libro) use ($q) {
        return str_contains(mb_strtolower($libro["titulo"] ?? '', 'UTF-8'), $q)
            || str_contains(mb_strtolower($libro["autor"] ?? '', 'UTF-8'), $q)
            || str_contains(mb_strtolower($libro["categoria"] ?? '', 'UTF-8'), $q);
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

                var today = new Date();
                var pad = function(n){ return String(n).padStart(2, '0'); };
                var todayStr = today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());

                Swal.fire({
                    title: 'Reservar libro',
                    html:
                        '<div style="display:flex;flex-direction:column;gap:12px;text-align:left;">' +
                            '<label style="font-weight:600;">Fecha desde' +
                                '<input id="swal-fecha-desde" type="date" class="swal2-input" style="margin:6px 0 0 0;width:100%;" value="' + todayStr + '" min="' + todayStr + '">' +
                            '</label>' +
                            '<label style="font-weight:600;">Fecha hasta' +
                                '<input id="swal-fecha-hasta" type="date" class="swal2-input" style="margin:6px 0 0 0;width:100%;" min="' + todayStr + '">' +
                            '</label>' +
                        '</div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Reservar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: function(){
                        var desde = document.getElementById('swal-fecha-desde').value;
                        var hasta = document.getElementById('swal-fecha-hasta').value;
                        if (!desde || !hasta) {
                            Swal.showValidationMessage('Debes seleccionar ambas fechas.');
                            return false;
                        }
                        if (hasta < desde) {
                            Swal.showValidationMessage('La fecha "hasta" debe ser mayor o igual a la fecha "desde".');
                            return false;
                        }
                        return { desde: desde, hasta: hasta };
                    }
                }).then(function(result){
                    if (result.isConfirmed && result.value) {
                        try {
                            var u = new URL(href, window.location.origin);
                            u.searchParams.set('desde', result.value.desde);
                            u.searchParams.set('hasta', result.value.hasta);
                            window.location.href = u.toString();
                        } catch (e) {
                            var join = href.indexOf('?') === -1 ? '?' : '&';
                            window.location.href = href + join + 'desde=' + encodeURIComponent(result.value.desde) + '&hasta=' + encodeURIComponent(result.value.hasta);
                        }
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
