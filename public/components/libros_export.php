<?php

if (!function_exists('libros_export_fetch')) {
    function libros_export_fetch($db)
    {
        $queries = [
            "
                SELECT
                    l.id,
                    l.titulo,
                    l.autor,
                    l.portada,
                    l.stock,
                    c.nombre AS categoria,
                    l.created_at AS created_at,
                    l.updated_at AS updated_at,
                    (
                        SELECT u.relative_path
                        FROM uploads u
                        WHERE u.stored_name = l.portada
                        ORDER BY u.id DESC
                        LIMIT 1
                    ) AS portada_path
                FROM libros l
                INNER JOIN categorias_libros c ON c.id = l.categoria_id
                ORDER BY l.titulo ASC
            ",
            "
                SELECT
                    l.id,
                    l.titulo,
                    l.autor,
                    l.portada,
                    l.stock,
                    c.nombre AS categoria,
                    l.fecha_creacion AS created_at,
                    l.fecha_actualizacion AS updated_at,
                    (
                        SELECT u.relative_path
                        FROM uploads u
                        WHERE u.stored_name = l.portada
                        ORDER BY u.id DESC
                        LIMIT 1
                    ) AS portada_path
                FROM libros l
                INNER JOIN categorias_libros c ON c.id = l.categoria_id
                ORDER BY l.titulo ASC
            ",
            "
                SELECT
                    l.id,
                    l.titulo,
                    l.autor,
                    l.portada,
                    l.stock,
                    c.nombre AS categoria,
                    (
                        SELECT u.relative_path
                        FROM uploads u
                        WHERE u.stored_name = l.portada
                        ORDER BY u.id DESC
                        LIMIT 1
                    ) AS portada_path
                FROM libros l
                INNER JOIN categorias_libros c ON c.id = l.categoria_id
                ORDER BY l.titulo ASC
            ",
        ];

        foreach ($queries as $sql) {
            try {
                $stmt = $db->query($sql);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                foreach ($rows as &$r) {
                    if (!array_key_exists('created_at', $r)) {
                        $r['created_at'] = null;
                    }
                    if (!array_key_exists('updated_at', $r)) {
                        $r['updated_at'] = null;
                    }
                    if (!array_key_exists('portada_path', $r)) {
                        $r['portada_path'] = null;
                    }
                }
                unset($r);

                return $rows;
            } catch (Exception $e) {
                continue;
            }
        }

        return [];
    }
}

if (!function_exists('libros_export_handle_export')) {
    function libros_export_handle_export($db, $options = [])
    {
        $paramExport = $options['param_export'] ?? 'export_libros';
        $format = strtolower(trim((string)($_GET['format'] ?? 'xls')));
        if (!in_array($format, ['xls', 'csv'], true)) {
            $format = 'xls';
        }

        if (!isset($_GET[$paramExport]) || $_GET[$paramExport] !== '1') {
            return;
        }

        $rows = [];
        try {
            $rows = libros_export_fetch($db);
        } catch (Exception $e) {
            $rows = [];
        }

        if ($format === 'csv') {
            $filename = 'libros_' . date('Ymd_His') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Título', 'Autor', 'Categoría', 'Stock', 'Creado', 'Actualizado', 'Portada (URL)'], ';');

            $fmtDateTime = function ($value) {
                if (!is_string($value) || trim($value) === '') {
                    return '';
                }
                $ts = strtotime($value);
                if ($ts === false) {
                    return (string)$value;
                }
                return date('d/m/Y H:i', $ts);
            };

            foreach ($rows as $r) {
                $portada = (string)($r['portada'] ?? '');
                $portadaPath = (string)($r['portada_path'] ?? '');
                $portadaUrl = '';
                if ($portadaPath !== '' && function_exists('url_for')) {
                    $portadaUrl = url_for(ltrim($portadaPath, '/'));
                } elseif ($portada !== '' && function_exists('url_for')) {
                    $portadaUrl = url_for('img/portadas/' . ltrim($portada, '/'));
                }

                fputcsv($out, [
                    $r['id'] ?? '',
                    $r['titulo'] ?? '',
                    $r['autor'] ?? '',
                    $r['categoria'] ?? '',
                    $r['stock'] ?? '',
                    $fmtDateTime((string)($r['created_at'] ?? '')),
                    $fmtDateTime((string)($r['updated_at'] ?? '')),
                    $portadaUrl,
                ], ';');
            }

            fclose($out);
            exit;
        }

        $filename = 'libros_' . date('Ymd_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";

        $fmtDateTime = function ($value) {
            if (!is_string($value) || trim($value) === '') {
                return '';
            }
            $ts = strtotime($value);
            if ($ts === false) {
                return (string)$value;
            }
            return date('d/m/Y H:i', $ts);
        };

        echo "<!DOCTYPE html>\n";
        echo "<html lang=\"es\">\n";
        echo "<head>\n";
        echo "<meta charset=\"UTF-8\">\n";
        echo "<title>Libros</title>\n";
        echo "<style>";
        echo "body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#222;}";
        echo "table{border-collapse:collapse;width:100%;}";
        echo "th,td{border:1px solid #c9c9c9;padding:6px 8px;vertical-align:top;}";
        echo "th{background:#f3ebe1;font-weight:bold;color:#4a3b28;}";
        echo "td.num{text-align:right;}";
        echo "</style>\n";
        echo "</head>\n";
        echo "<body>\n";

        echo "<h2>Listado de libros</h2>\n";

        echo "<table>\n";
        echo "<thead>\n";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Título</th>";
        echo "<th>Autor</th>";
        echo "<th>Categoría</th>";
        echo "<th>Stock</th>";
        echo "<th>Creado</th>";
        echo "<th>Actualizado</th>";
        echo "<th>Portada (URL)</th>";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        foreach ($rows as $r) {
            $portada = (string)($r['portada'] ?? '');
            $portadaPath = (string)($r['portada_path'] ?? '');
            $portadaUrl = '';
            if ($portadaPath !== '' && function_exists('url_for')) {
                $portadaUrl = url_for(ltrim($portadaPath, '/'));
            } elseif ($portada !== '' && function_exists('url_for')) {
                $portadaUrl = url_for('img/portadas/' . ltrim($portada, '/'));
            }

            echo "<tr>";
            echo "<td class=\"num\">" . htmlspecialchars((string)($r['id'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars((string)($r['titulo'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars((string)($r['autor'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars((string)($r['categoria'] ?? '')) . "</td>";
            echo "<td class=\"num\">" . htmlspecialchars((string)($r['stock'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars($fmtDateTime((string)($r['created_at'] ?? ''))) . "</td>";
            echo "<td>" . htmlspecialchars($fmtDateTime((string)($r['updated_at'] ?? ''))) . "</td>";
            echo "<td>" . htmlspecialchars((string)$portadaUrl) . "</td>";
            echo "</tr>\n";
        }

        echo "</tbody>\n";
        echo "</table>\n";

        echo "</body>\n";
        echo "</html>\n";
        exit;
    }
}
