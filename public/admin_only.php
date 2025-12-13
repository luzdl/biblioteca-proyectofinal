<?php
require_once 'auth.php';

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
    header("HTTP/1.1 403 Forbidden");
    echo "⛔ No tienes permiso para acceder a esta sección. Solo administradores.";
    exit;
}
