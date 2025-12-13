<?php
// scripts/migrate_db.php
// Uso: php scripts\migrate_db.php
// Este script aplica el SQL idempotente `biblioteca_digital_idempotent.sql` usando las variables de entorno.

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

// Cargar archivo SQL
$sqlFile = __DIR__ . '/../biblioteca_digital_idempotent.sql';
if (!is_file($sqlFile)) {
    echo "No se encontró el archivo: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "No se pudo leer el archivo SQL.\n";
    exit(1);
}

try {
    $db = (new Database())->getConnection();

    // Separar por ';' - esto es suficiente para dumps simples
    $statements = preg_split('/;\s*\n/', $sql);
    $executed = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' ) continue;
        // Evitar comentarios del tipo -- ...
        if (str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) continue;
        try {
            $db->exec($stmt);
            $executed++;
        } catch (PDOException $e) {
            // Mostrar pero continuar (algunos comandos pueden no ser aplicables)
            echo "WARNING: falla ejecutando statement: " . substr($stmt,0,120) . "...\n";
            echo "  -> " . $e->getMessage() . "\n";
        }
    }

    echo "Migración completada. Sentencias ejecutadas: $executed\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
