<?php
// Simple router helper: map route names to internal paths and provide redirect helper
function router_routes()
{
    return [
        'home' => 'public/catalog.php',
        'login' => 'public/login.php',
        'register' => 'public/registro.php',
        'student' => 'public/app/student/catalog.php',
        'student_reservas' => 'public/app/student/reservas.php',
        'student_historial' => 'public/app/student/historial.php',
        'perfil' => 'public/app/profile/index.php',
        'admin' => 'public/app/admin/index.php',
        'staff' => 'public/app/staff/index.php',
        'reservar' => 'public/reservar.php',
    ];
}

function route($nameOrPath, $params = [])
{
    $routes = router_routes();

    // If name exists in routes, use it; else treat as raw path

    $path = $routes[$nameOrPath] ?? null;

    if ($path === null) {
        // Not a named route. If it's a PHP file path (e.g., "reservar.php"),
        // treat it as relative to /public/ so redirects from /public/login.php
        // go to the correct file. If it already starts with '/' keep it.
        if (stripos($nameOrPath, '.php') !== false) {
            if (strpos($nameOrPath, '/') === 0) {
                // strip leading slash to keep paths project-relative
                $path = ltrim($nameOrPath, '/');
            } elseif (stripos($nameOrPath, 'public/') === 0) {
                // already project-relative
                $path = $nameOrPath;
            } else {
                $path = 'public/' . ltrim($nameOrPath, '/');
            }
        } else {
            // fallback: project-relative
            $path = ltrim($nameOrPath, '/');
        }
    }

    if (!empty($params)) {
        $query = http_build_query($params);
        $path .= (strpos($path, '?') === false ? '?' : '&') . $query;
    }

    return $path;
}

function redirect($nameOrPath, $params = [])
{
    $target = route($nameOrPath, $params);

    // Prevent open redirects: only allow local paths
    if (stripos($target, 'http://') === 0 || stripos($target, 'https://') === 0) {
        // fallback to home
        $target = route('home');
    }

    // Build absolute URL to avoid relative resolution issues (e.g. public/public/...)
    $scheme = (!empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http'));
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Determine project base (strip '/public' and everything after if present in script path)
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $pos = strpos($script, '/public');
    if ($pos !== false) {
        $projectBase = substr($script, 0, $pos);
    } else {
        // fallback: use directory of script
        $projectBase = rtrim(dirname($script), '/');
    }

    // Ensure leading slash
    if ($projectBase === '') {
        $projectBase = '';
    }

    // Make absolute URL and normalize slashes
    $path = '/' . ltrim($target, '/');
    $url = $scheme . '://' . $host . $projectBase . $path;

    header('Location: ' . $url);
    exit;
}

function url_for($nameOrPath, $params = [])
{
    $target = route($nameOrPath, $params);

    // Prevent open redirects: only allow local paths
    if (stripos($target, 'http://') === 0 || stripos($target, 'https://') === 0) {
        $target = route('home');
    }

    $scheme = (!empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http'));
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $pos = strpos($script, '/public');
    if ($pos !== false) {
        $projectBase = substr($script, 0, $pos);
    } else {
        $projectBase = rtrim(dirname($script), '/');
    }

    if ($projectBase === '') {
        $projectBase = '';
    }

    $path = '/' . ltrim($target, '/');
    return $scheme . '://' . $host . $projectBase . $path;
}
