<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'bibliotecario') {
    header("Location: ../public/login.php");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->getConnection();

/* Contadores del panel */
$totalLibros = $db->query("SELECT COUNT(*) AS total FROM libros")->fetch()['total'];
$totalCategorias = $db->query("SELECT COUNT(*) AS total FROM categorias_libros")->fetch()['total'];
$totalReservas = $db->query("SELECT COUNT(*) AS total FROM reservas")->fetch()['total'];
$reservasPendientes = $db->query("SELECT COUNT(*) AS total FROM reservas WHERE estado='pendiente'")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Bibliotecario</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/bibliotecario.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
</head>
<body>

<?php 
$active = "dashboard";
include "sidebar.php"; 
?>

<main class="content">

    <h1 class="page-title">Panel del Bibliotecario</h1>
    <p class="subtitle">Resumen general de la biblioteca</p>

    <div class="dashboard-grid">

        <div class="dash-card">
            <span class="material-symbols-outlined icon">menu_book</span>
            <h2><?= $totalLibros ?></h2>
            <p>Libros registrados</p>
        </div>

        <div class="dash-card">
            <span class="material-symbols-outlined icon">category</span>
            <h2><?= $totalCategorias ?></h2>
            <p>Categorías disponibles</p>
        </div>

        <div class="dash-card">
            <span class="material-symbols-outlined icon">list_alt</span>
            <h2><?= $totalReservas ?></h2>
            <p>Reservas totales</p>
        </div>

        <div class="dash-card pending">
            <span class="material-symbols-outlined icon">hourglass_top</span>
            <h2><?= $reservasPendientes ?></h2>
            <p>Reservas pendientes</p>
        </div>

    </div>

</main>

</body>
</html>
