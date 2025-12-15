<?php
if (!function_exists('reservas_report_normalize_date')) {
    function reservas_report_normalize_date($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt) {
            return null;
        }

        return $dt->format('Y-m-d');
    }
}

if (!function_exists('reservas_report_build_query')) {
    function reservas_report_build_query($filters)
    {
        $sql = "
            SELECT
                r.id,
                l.titulo AS libro,
                u.usuario AS usuario,
                u.rol AS rol_usuario,
                r.estado,
                r.fecha_reserva,
                r.fecha_limite,
                r.fecha_devolucion,
                DATEDIFF(COALESCE(r.fecha_devolucion, CURDATE()), r.fecha_reserva) AS dias_reservados
            FROM reservas r
            INNER JOIN libros l ON l.id = r.libro_id
            INNER JOIN usuarios u ON u.id = r.usuario_id
        ";

        $where = [];
        $params = [];

        if (!empty($filters['desde'])) {
            $where[] = 'r.fecha_reserva >= :desde';
            $params[':desde'] = $filters['desde'];
        }

        if (!empty($filters['hasta'])) {
            $where[] = 'r.fecha_reserva <= :hasta';
            $params[':hasta'] = $filters['hasta'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'r.estado = :estado';
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY r.fecha_reserva DESC, r.id DESC';

        return [$sql, $params];
    }
}

if (!function_exists('reservas_report_fetch')) {
    function reservas_report_fetch($db, $filters)
    {
        [$sql, $params] = reservas_report_build_query($filters);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('reservas_report_handle_export')) {
    function reservas_report_handle_export($db, $options = [])
    {
        $paramExport = $options['param_export'] ?? 'export_reservas';

        if (!isset($_GET[$paramExport]) || $_GET[$paramExport] !== '1') {
            return;
        }

        $filters = [
            'desde' => reservas_report_normalize_date($_GET['desde'] ?? ''),
            'hasta' => reservas_report_normalize_date($_GET['hasta'] ?? ''),
            'estado' => trim((string)($_GET['estado'] ?? '')),
        ];

        $rows = reservas_report_fetch($db, $filters);

        $filename = 'reporte_reservas_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        fputcsv($out, [
            'ID',
            'Libro',
            'Usuario',
            'Rol',
            'Estado',
            'Fecha reserva',
            'Fecha límite',
            'Fecha devolución',
            'Días reservados'
        ], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['libro'],
                $r['usuario'],
                $r['rol_usuario'],
                $r['estado'],
                $r['fecha_reserva'],
                $r['fecha_limite'],
                $r['fecha_devolucion'],
                $r['dias_reservados'],
            ], ';');
        }

        fclose($out);
        exit;
    }
}

if (!function_exists('reservas_report_render')) {
    function reservas_report_render($db, $options = [])
    {
        $pagePath = (string)($options['page_path'] ?? 'app/profile/index.php');
        $defaultActionUrl = function_exists('url_for') ? url_for($pagePath) : '';
        $actionUrl = $options['action_url'] ?? $defaultActionUrl;
        $paramExport = $options['param_export'] ?? 'export_reservas';

        $filters = [
            'desde' => reservas_report_normalize_date($_GET['desde'] ?? ''),
            'hasta' => reservas_report_normalize_date($_GET['hasta'] ?? ''),
            'estado' => trim((string)($_GET['estado'] ?? '')),
        ];

        $rows = reservas_report_fetch($db, $filters);

        $exportParams = [];
        if (!empty($filters['desde'])) {
            $exportParams['desde'] = $filters['desde'];
        }
        if (!empty($filters['hasta'])) {
            $exportParams['hasta'] = $filters['hasta'];
        }
        if (!empty($filters['estado'])) {
            $exportParams['estado'] = $filters['estado'];
        }
        $exportParams[$paramExport] = 1;

        $exportUrl = function_exists('url_for') ? url_for($pagePath, $exportParams) : '';

        $estados = ['pendiente', 'en_curso', 'finalizado', 'cancelado'];

        ?>
        <section class="profile-actions reservas-report" id="reporte-reservas">
            <h2 class="subtitle">Reporte de reservas</h2>

            <form method="get" action="<?php echo htmlspecialchars($actionUrl); ?>" class="reservas-report-form">
                <div class="reservas-report-row">
                    <label>
                        Desde
                        <input type="date" name="desde" value="<?php echo htmlspecialchars($filters['desde'] ?? ''); ?>">
                    </label>

                    <label>
                        Hasta
                        <input type="date" name="hasta" value="<?php echo htmlspecialchars($filters['hasta'] ?? ''); ?>">
                    </label>

                    <label>
                        Estado
                        <select name="estado">
                            <option value="">Todos</option>
                            <?php foreach ($estados as $e): ?>
                                <option value="<?php echo htmlspecialchars($e); ?>" <?php echo ($filters['estado'] === $e ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($e); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="reservas-report-actions">
                    <button type="submit" class="reservas-report-btn">Filtrar</button>
                    <a class="reservas-report-btn reservas-report-btn-secondary" href="<?php echo htmlspecialchars($exportUrl); ?>">Exportar (Excel)</a>
                </div>
            </form>

            <div class="reservas-report-table-wrap">
                <?php if (count($rows) === 0): ?>
                    <p>No hay reservas para los filtros seleccionados.</p>
                <?php else: ?>
                    <table class="reservas-report-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libro</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha reserva</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['id']); ?></td>
                                <td><?php echo htmlspecialchars($r['libro']); ?></td>
                                <td><?php echo htmlspecialchars($r['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($r['rol_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($r['estado']); ?></td>
                                <td><?php echo htmlspecialchars($r['fecha_reserva']); ?></td>
                                <td><?php echo htmlspecialchars($r['dias_reservados']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
