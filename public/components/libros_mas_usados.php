<?php

if (!function_exists('libros_mas_usados_normalize_date')) {
    function libros_mas_usados_normalize_date($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return '';
        }
        return date('Y-m-d', $ts);
    }
}

if (!function_exists('libros_mas_usados_fetch')) {
    function libros_mas_usados_fetch($db, $filters = [], $limit = 5)
    {
        $where = [];
        $params = [];

        if (!empty($filters['desde'])) {
            $where[] = 'DATE(r.fecha_reserva) >= :desde';
            $params[':desde'] = $filters['desde'];
        }

        if (!empty($filters['hasta'])) {
            $where[] = 'DATE(r.fecha_reserva) <= :hasta';
            $params[':hasta'] = $filters['hasta'];
        }

        $where[] = "r.estado <> 'cancelado'";

        $sql = "
            SELECT
                l.id,
                l.titulo,
                l.autor,
                COUNT(*) AS total
            FROM reservas r
            INNER JOIN libros l ON l.id = r.libro_id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY l.id, l.titulo, l.autor
            ORDER BY total DESC, l.titulo ASC
            LIMIT :limit
        ";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('libros_mas_usados_render')) {
    function libros_mas_usados_render($db, $options = [])
    {
        $paramDesde = $options['param_desde'] ?? 'stats_desde';
        $paramHasta = $options['param_hasta'] ?? 'stats_hasta';
        $limit = (int)($options['limit'] ?? 5);
        if ($limit <= 0) {
            $limit = 5;
        }

        $preserveParams = $options['preserve_params'] ?? [];
        if (!is_array($preserveParams)) {
            $preserveParams = [];
        }

        $defaultHasta = date('Y-m-d');
        $defaultDesde = date('Y-m-d', strtotime('-30 days'));

        $filters = [
            'desde' => libros_mas_usados_normalize_date($_GET[$paramDesde] ?? $defaultDesde),
            'hasta' => libros_mas_usados_normalize_date($_GET[$paramHasta] ?? $defaultHasta),
        ];

        $rows = [];
        try {
            $rows = libros_mas_usados_fetch($db, $filters, $limit);
        } catch (Exception $e) {
            $rows = [];
        }

        $max = 0;
        foreach ($rows as $r) {
            $max = max($max, (int)($r['total'] ?? 0));
        }

        ?>
        <section class="lmu-card" aria-labelledby="lmu-title">
            <div class="lmu-header">
                <div>
                    <h2 id="lmu-title" class="lmu-title">Estadísticas</h2>
                    <p class="lmu-subtitle">Libros más usados por período</p>
                </div>

                <form class="lmu-filters" method="get">
                    <?php foreach ($preserveParams as $k): ?>
                        <?php if (isset($_GET[$k]) && !is_array($_GET[$k])): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars((string)$k); ?>" value="<?php echo htmlspecialchars((string)$_GET[$k]); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <label class="lmu-field">
                        <span>Desde</span>
                        <input type="date" name="<?php echo htmlspecialchars((string)$paramDesde); ?>" value="<?php echo htmlspecialchars((string)$filters['desde']); ?>">
                    </label>

                    <label class="lmu-field">
                        <span>Hasta</span>
                        <input type="date" name="<?php echo htmlspecialchars((string)$paramHasta); ?>" value="<?php echo htmlspecialchars((string)$filters['hasta']); ?>">
                    </label>

                    <button type="submit" class="lmu-btn">Aplicar</button>
                </form>
            </div>

            <div class="lmu-body">
                <?php if (empty($rows)): ?>
                    <div class="lmu-empty">No hay datos para el período seleccionado.</div>
                <?php else: ?>
                    <div class="lmu-chart" role="img" aria-label="Gráfico de barras de libros más usados">
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $total = (int)($r['total'] ?? 0);
                                $pct = $max > 0 ? round(($total / $max) * 100, 2) : 0;
                                $label = trim((string)($r['titulo'] ?? ''));
                                $autor = trim((string)($r['autor'] ?? ''));
                                $caption = $autor !== '' ? ($label . ' — ' . $autor) : $label;
                            ?>
                            <div class="lmu-row">
                                <div class="lmu-label" title="<?php echo htmlspecialchars($caption); ?>">
                                    <div class="lmu-book-title"><?php echo htmlspecialchars($label); ?></div>
                                    <?php if ($autor !== ''): ?>
                                        <div class="lmu-book-author"><?php echo htmlspecialchars($autor); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="lmu-bar">
                                    <div class="lmu-bar-fill" style="width: <?php echo htmlspecialchars((string)$pct); ?>%"></div>
                                </div>
                                <div class="lmu-value"><?php echo htmlspecialchars((string)$total); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
