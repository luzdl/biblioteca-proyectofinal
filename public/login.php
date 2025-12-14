<?php
require_once __DIR__ . '/../config/env.php';

session_start();

require_once __DIR__ . '/../config/router.php';

require_once __DIR__ . '/../config/database.php';

$mensaje = "";
$tipoMensaje = ""; // "error" o "exito"

$usuarioOEmail = $_POST["usuario_email"] ?? "";
$rolSolicitado = $_POST["rol"] ?? "";

// Parámetro que indica a dónde redirigir tras login (p. ej. reservar.php?id=2)
$next = '';
if (isset($_REQUEST['next'])) {
    $next = $_REQUEST['next'];
}

// Si ya está logueado, redirigir según rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_rol'] === "administrador") {
        redirect('admin');
    } elseif ($_SESSION['usuario_rol'] === "bibliotecario") {
        redirect('staff');
    } else {
        redirect('student');
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuarioOEmail = trim($_POST["usuario_email"] ?? "");
    $password      = $_POST["password"] ?? "";
    $rolSolicitado = $_POST["rol"] ?? "";

    if ($usuarioOEmail === "" || $password === "" || $rolSolicitado === "") {
        $mensaje = "Por favor, completa todos los campos.";
        $tipoMensaje = "error";
    } 
    elseif (!in_array($rolSolicitado, ['estudiante', 'administrador', 'bibliotecario'], true)) {
        $mensaje = "Rol inválido.";
        $tipoMensaje = "error";
    } 
    else {
        try {
            $db = (new Database())->getConnection();

            // Buscar por email o usuario Y por rol
            $sql = "SELECT id, usuario, email, password_hash, rol
                    FROM usuarios
                    WHERE (email = :valor OR usuario = :valor)
                    AND rol = :rol
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':valor' => $usuarioOEmail,
                ':rol'   => $rolSolicitado,
            ]);

            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {

                // Guardar sesión
                $_SESSION['usuario_id']      = $usuario['id'];
                $_SESSION['usuario_usuario'] = $usuario['usuario'];
                $_SESSION['usuario_email']   = $usuario['email'];
                $_SESSION['usuario_rol']     = $usuario['rol'];
                // Si vino un "next" válido, redirigir allí (solo rutas internas)
                $postedNext = $_POST['next'] ?? $next;
                if ($postedNext) {
                    // Ensure we have a decoded, project-relative path
                    $postedNext = urldecode($postedNext);
                    $lower = strtolower($postedNext);
                    if (strpos($lower, 'http://') === false && strpos($lower, 'https://') === false && strpos($postedNext, '..') === false) {
                        redirect($postedNext);
                    }
                }

                // Redirigir según rol por defecto
                switch ($usuario['rol']) {
                    case "administrador":
                        redirect('admin');
                        break;
                    case "bibliotecario":
                        redirect('staff');
                        break;
                    case "estudiante":
                        redirect('student');
                        break;
                }
                exit;
            } 
            else {
                $mensaje = "Credenciales inválidas para el rol seleccionado.";
                $tipoMensaje = "error";
            }

        } catch (Exception $e) {
            $mensaje = "Error interno: " . $e->getMessage();
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

    <!-- LADO IZQUIERTO -->
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
                <div class="alert <?php echo $tipoMensaje === 'error' ? 'alert-error' : 'alert-success'; ?>">
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
                        value="<?php echo htmlspecialchars($usuarioOEmail); ?>"
                    >
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                    >
                </div>

                <div class="field field-select">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol">
                        <option value="">Seleccione un rol</option>

                        <option value="estudiante"
                            <?php echo $rolSolicitado === 'estudiante' ? 'selected' : ''; ?>>
                            Estudiante
                        </option>

                        <option value="bibliotecario"
                            <?php echo $rolSolicitado === 'bibliotecario' ? 'selected' : ''; ?>>
                            Bibliotecario
                        </option>

                        <option value="administrador"
                            <?php echo $rolSolicitado === 'administrador' ? 'selected' : ''; ?>>
                            Administrador
                        </option>
                    </select>
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
