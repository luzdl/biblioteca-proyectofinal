<?php
// Book card component
// Expects $book array and optional $extraHtml string
$extraHtml = $extraHtml ?? '';
?>
<div class="book-card">
    <div class="book-image">
        <img src="<?php echo htmlspecialchars($book['imagen'] ?? (function_exists('url_for') ? url_for('img/placeholder.png') : '../img/placeholder.png')); ?>" alt="Portada">
    </div>

    <h3 class="book-title"><?php echo htmlspecialchars($book['titulo'] ?? 'Título'); ?></h3>

    <p class="book-author"><?php echo htmlspecialchars($book['autor'] ?? 'Autor'); ?></p>

    <?php if (!empty($book['categoria'])): ?>
        <p class="book-category"><?php echo htmlspecialchars($book['categoria']); ?></p>
    <?php endif; ?>

    <?php echo $extraHtml; ?>
</div>
