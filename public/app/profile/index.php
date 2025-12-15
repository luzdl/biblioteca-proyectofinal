<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_login();

$db = (new Database())->getConnection();
$user = fetch_current_user($db);

/*
 * Datos base del perfil
 */
$profileData = $user ?: [
    'usuario' => $_SESSION['usuario_usuario'] ?? '',
    'email'   => $_SESSION['usuario_email'] ?? '',
    'rol'     => $_SESSION['usuario_rol'] ?? '',
];

/* Inicial del usuario (joseab → J) */
$inicial = strtoupper(substr($profileData['usuario'] ?? 'U', 0, 1));
$rol = (string)($profileData['rol'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('css/student_reservas.css')); ?>">

    <style>
        /* ==========================
           PERFIL
        ========================== */

        .profile-card {
            background: white;
            border-radius: 22px;
            padding: 40px;
            display: flex;
            gap: 40px;
            align-items: center;
            box-shadow: 0 10px 26px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c7a56b, #b7874b);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 14px 30px rgba(0,0,0,0.18);
        }

        .avatar-letter {
            font-size: 64px;
            font-weight: 700;
            color: #ffffff;
            user-select: none;
        }

        .profile-info h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #4a3b28;
        }

        .profile-info p {
            font-size: 16px;
            color: #6b5944;
            margin-bottom: 6px;
        }

        .profile-info .badge {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 14px;
            border-radius: 20px;
            background: #e7dbc8;
            color: #4a3b28;
            font-weight: 600;
            font-size: 14px;
        }

        .profile-actions {
            background: white;
            border-radius: 22px;
            padding: 30px 40px;
            box-shadow: 0 10px 26px rgba(0,0,0,0.08);
        }

        .profile-actions ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .profile-actions li {
            margin-bottom: 14px;
        }

        .profile-actions a {
            text-decoration: none;
            font-weight: 600;
            color: #b7874b;
            transition: 0.25s;
        }

        .profile-actions a:hover {
            color: #8a6236;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/topbar.php'; ?>

<main class="content">
    <h1 class="title">Mi perfil</h1>

    <!-- PERFIL -->
    <section class="profile-card">
        <div class="profile-avatar">
            <div class="avatar-letter">
                <?php echo htmlspecialchars($inicial); ?>
            </div>
        </div>

        <div class="profile-info">
            <h2><?php echo htmlspecialchars($profileData['usuario']); ?></h2>
            <p><?php echo htmlspecialchars($profileData['email']); ?></p>
            <span class="badge">
                <?php echo ucfirst(htmlspecialchars($rol)); ?>
            </span>
        </div>
    </section>

    <!-- ACCIONES -->
    <section class="profile-actions">
        <h2 class="subtitle">Acciones</h2>

        <ul>
            <?php if ($rol === 'administrador'): ?>
                <li><a href="<?php echo htmlspecialchars(url_for('app/admin/categorias.php')); ?>">Gestionar categorías</a></li>
                <li><a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>">Gestionar carreras</a></li>
            <?php endif; ?>

            <?php if ($rol === 'bibliotecario'): ?>
                <li><a href="<?php echo htmlspecialchars(url_for('app/admin/carreras.php')); ?>">Gestionar carreras</a></li>
            <?php endif; ?>

            <?php if ($rol === 'estudiante'): ?>
                <li><a href="<?php echo htmlspecialchars(url_for('app/student/reservas.php')); ?>">Ver mis reservas</a></li>
                <li><a href="<?php echo htmlspecialchars(url_for('app/student/historial.php')); ?>">Ver historial</a></li>
            <?php endif; ?>
        </ul>
    </section>

</main>

</body>
</html>
