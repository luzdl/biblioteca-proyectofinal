<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/router.php';
require_once __DIR__ . '/../config/database.php';

session_start();

$mensaje = "";
$tipoMensaje = ""; // error | exito

// Para redirección después de login
$next = '';
if (isset($_REQUEST['next'])) {
    $next = $_REQUEST['next'];
}

// Si ya está logueado, redirigir según rol
if (isset($_SESSION['usuario_id'])) {
    switch ($_SESSION['usuario_rol']) {
        case 'administrador':
            redirect('admin');
            break;
        case 'bibliotecario':
            redirect('staff');
            break;
        default:
            redirect('student');
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuarioOEmail = trim($_POST["usuario_email"] ?? "");
    $password      = $_POST["password"] ?? "";

    if ($usuarioOEmail === "" || $password === "") {
        $mensaje = "Por favor, completa todos los campos.";
        $tipoMensaje = "error";
    } else {

        try {
            $db = (new Database())->getConnection();

            // Buscar SOLO por usuario o email
            $sql = "SELECT id, usuario, email, password_hash, rol
                    FROM usuarios
                    WHERE email = :valor OR usuario = :valor
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':valor' => $usuarioOEmail
            ]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {

                // Guardar sesión
                $_SESSION['usuario_id']      = $usuario['id'];
                $_SESSION['usuario_usuario'] = $usuario['usuario'];
                $_SESSION['usuario_email']   = $usuario['email'];
                $_SESSION['usuario_rol']     = $usuario['rol'];

                // Redirección personalizada si viene "next"
                $postedNext = $_POST['next'] ?? $next;
                if ($postedNext) {
                    $postedNext = urldecode($postedNext);
                    $lower = strtolower($postedNext);

                    if (
                        strpos($lower, 'http://') === false &&
                        strpos($lower, 'https://') === false &&
                        strpos($postedNext, '..') === false
                    ) {
                        redirect($postedNext);
                        exit;
                    }
                }

                // Redirección por rol
                switch ($usuario['rol']) {
                    case 'administrador':
                        redirect('admin');
                        break;
                    case 'bibliotecario':
                        redirect('staff');
                        break;
                    default:
                        redirect('student');
                }
                exit;

            } else {
                $mensaje = "Usuario o contraseña incorrectos.";
                $tipoMensaje = "error";
            }

        } catch (Exception $e) {
            $mensaje = "Error interno del sistema.";
            $tipoMensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión | Biblioteca Digital</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="../css/components.css">
</head>
<body>
<div class="auth-layout">

    <!-- LADO IZQUIERDO -->
    <section class="hero-side">
        <div class="hero-overlay">
            <h2 class="hero-title heading-serif">
                Biblioteca<br>Digital
            </h2>
            <p class="hero-text">
                Accede a tus libros y recursos en cualquier momento y desde cualquier lugar.
            </p>
        </div>
    </section>

    <!-- FORMULARIO -->
    <section class="form-side">
        <div class="form-wrapper">
            <p class="overline heading-serif">Bienvenido de nuevo</p>
            <h1 class="main-title heading-serif">Inicia sesión</h1>
            <p class="subtitle">
                Ingresa para acceder a la biblioteca digital.
            </p>

            <?php if ($mensaje): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">

                <div class="field">
                    <label for="usuario_email">Usuario o correo</label>
                    <input
                        type="text"
                        id="usuario_email"
                        name="usuario_email"
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($usuarioOEmail ?? ''); ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn-primary">
                        Entrar
                    </button>
                </div>
            </form>

            <p class="small-text">
                ¿Aún no tienes cuenta?
                <a href="registro.php">Crear una cuenta</a>
            </p>
        </div>
    </section>
</div>
</body>
</html>
