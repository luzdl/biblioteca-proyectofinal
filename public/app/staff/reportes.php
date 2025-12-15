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
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../config/env.php";

$db = (new Database())->getConnection();

/* ==============================
   DATOS GENERALES
   ============================== */
$totalLibros = $db->query(
    "SELECT COUNT(*) FROM libros"
)->fetchColumn();

$totalUsuarios = $db->query(
    "SELECT COUNT(*) FROM usuarios"
)->fetchColumn();

/* ==============================
   RESERVAS POR ESTADO
   ============================== */
$reservasPorEstado = $db->query(
    "SELECT estado, COUNT(*) total
     FROM reservas
     GROUP BY estado"
)->fetchAll();

/* ==============================
   LIBROS MÁS RESERVADOS
   ============================== */
$librosPopulares = $db->query(
    "SELECT l.titulo, COUNT(r.id) total
     FROM reservas r
     JOIN libros l ON l.id = r.libro_id
     GROUP BY l.id
     ORDER BY total DESC
     LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>

    <!-- ESTILOS -->
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../../../css/bibliotecario.css">

    <!-- ICONOS -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>

<?php
$active = "reportes";
include __DIR__ . "/sidebar.php";
?>

<main class="content">

    <h1 class="page-title">Reportes</h1>

    <!-- TARJETAS -->
    <div class="dashboard-grid">

        <div class="dash-card">
            <span class="material-symbols-outlined icon">menu_book</span>
            <h2><?= $totalLibros ?></h2>
            <p>Libros registrados</p>
        </div>

        <div class="dash-card">
            <span class="material-symbols-outlined icon">group</span>
            <h2><?= $totalUsuarios ?></h2>
            <p>Usuarios</p>
        </div>

    </div>

    <!-- RESERVAS POR ESTADO -->
    <section class="report-section">
        <h2>Reservas por estado</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reservasPorEstado as $r): ?>
                <tr>
                    <td><?= ucfirst($r['estado']) ?></td>
                    <td><?= $r['total'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- LIBROS MÁS RESERVADOS -->
    <section class="report-section">
        <h2>Libros más reservados</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Libro</th>
                    <th>Reservas</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($librosPopulares as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['titulo']) ?></td>
                    <td><?= $l['total'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</main>

</body>
</html>
