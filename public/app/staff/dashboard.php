<?php
session_start();

/* ==============================
   VALIDAR ACCESO DEL BIBLIOTECARIO
   ============================== */
if (
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'bibliotecario'
) {
    header("Location: ../../login.php");
    exit;
}

/* ==============================
   CONEXIÓN A LA BASE DE DATOS
   ============================== */
/*
 Estructura real:
 public/app/staff/dashboard.php
 ↑↑↑
 config/database.php
*/
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../config/env.php";

$db = (new Database())->getConnection();

/* ==============================
   OBTENER ESTADÍSTICAS
   ============================== */
$totalLibros = $db->query(
    "SELECT COUNT(*) AS total FROM libros"
)->fetch()['total'];

$totalCategorias = $db->query(
    "SELECT COUNT(*) AS total FROM categorias_libros"
)->fetch()['total'];

$totalReservas = $db->query(
    "SELECT COUNT(*) AS total FROM reservas"
)->fetch()['total'];

$reservasPendientes = $db->query(
    "SELECT COUNT(*) AS total FROM reservas WHERE estado = 'pendiente'"
)->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Bibliotecario</title>

    <!-- ESTILOS -->
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../../../css/bibliotecario.css">

    <!-- ICONOS -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body>

<?php
    /* Sidebar unificado */
    $active = "dashboard";
    include __DIR__ . "/sidebar.php";
?>

<main class="content">

    <h1 class="page-title">Panel del Bibliotecario</h1>
    <p class="subtitle">Resumen general de la biblioteca</p>

    <div class="dashboard-grid">

        <!-- Tarjeta: Libros -->
        <div class="dash-card">
            <span class="material-symbols-outlined icon">menu_book</span>
            <h2><?= $totalLibros ?></h2>
            <p>Libros registrados</p>
        </div>

        <!-- Tarjeta: Categorías -->
        <div class="dash-card">
            <span class="material-symbols-outlined icon">category</span>
            <h2><?= $totalCategorias ?></h2>
            <p>Categorías disponibles</p>
        </div>

        <!-- Tarjeta: Reservas -->
        <div class="dash-card">
            <span class="material-symbols-outlined icon">list_alt</span>
            <h2><?= $totalReservas ?></h2>
            <p>Reservas totales</p>
        </div>

        <!-- Tarjeta: Pendientes -->
        <div class="dash-card pending">
            <span class="material-symbols-outlined icon">hourglass_top</span>
            <h2><?= $reservasPendientes ?></h2>
            <p>Reservas pendientes</p>
        </div>

    </div>

</main>

</body>
</html>
