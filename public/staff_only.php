<?php
require_once 'auth.php';

if (!in_array($_SESSION['usuario_rol'] ?? '', ['administrador', 'bibliotecario'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "⛔ Solo administradores o bibliotecarios pueden acceder a esta sección.";
    exit;
}
