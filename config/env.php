<?php
// Carga variables de entorno desde .env o .env.example (desarrollo)
// Coloca este archivo con `require_once` antes de instanciar Database

function load_dotenv_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $vars = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Remove surrounding quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $vars[$name] = $value;
    }
    return $vars;
}

$root = dirname(__DIR__);
$envPath = $root . DIRECTORY_SEPARATOR . '.env';
$examplePath = $root . DIRECTORY_SEPARATOR . '.env.example';

$envVars = [];
if (is_file($envPath)) {
    $envVars = load_dotenv_file($envPath);
} elseif (is_file($examplePath)) {
    // En desarrollo, si no existe .env, cargamos .env.example para evitar errores
    $envVars = load_dotenv_file($examplePath);
}

foreach ($envVars as $k => $v) {
    // No sobreescribir variables ya definidas en el entorno del servidor
    if (getenv($k) === false) {
        putenv($k . '=' . $v);
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}

// fin de env.php
