<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/User.php';

$db = (new Database())->getConnection();

// Traer carreras de la base de datos
$carrerasStmt = $db->query("SELECT id, nombre FROM carreras ORDER BY nombre");
$carreras = $carrerasStmt->fetchAll();

$mensaje = "";
$tipoMensaje = ""; // "error" o "exito"

// Para repoblar el formulario si hay error
$formData = [
    'cip'              => '',
    'primer_nombre'    => '',
    'segundo_nombre'   => '',
    'primer_apellido'  => '',
    'segundo_apellido' => '',
    'fecha_nacimiento' => '',
    'carrera_id'       => '',
    'usuario'          => '',
    'email'            => '',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Cargar datos del formulario en el array
    foreach ($formData as $key => $_) {
        $formData[$key] = trim($_POST[$key] ?? '');
    }

    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones básicas
    $camposObligatorios = [
        'cip',
        'primer_nombre',
        'primer_apellido',
        'fecha_nacimiento',
        'carrera_id',
        'usuario',
        'email',
    ];

    $hayVacios = false;
    foreach ($camposObligatorios as $campo) {
        if ($formData[$campo] === '') {
            $hayVacios = true;
            break;
        }
    }

    if ($hayVacios || $password === '' || $password2 === '') {
        $mensaje = "Por favor, completa todos los campos obligatorios.";
        $tipoMensaje = "error";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Ingresa un correo electrónico válido.";
        $tipoMensaje = "error";
    } elseif ($password !== $password2) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipoMensaje = "error";
    } else {
        try {
            $userData = $formData;
            $userData['password'] = $password;

            $user = new User($userData);
            $resultado = $user->save($db);

            if ($resultado === true) {
                $mensaje = "Usuario registrado correctamente. Ahora puedes iniciar sesión.";
                $tipoMensaje = "exito";

                // Limpiar campos del formulario
                foreach ($formData as $key => $_) {
                    $formData[$key] = '';
                }
            } else {
                // $resultado trae el mensaje de error de User::save()
                $mensaje = $resultado;
                $tipoMensaje = "error";
            }
        } catch (Exception $e) {
            $mensaje = "Ocurrió un error al registrar el usuario.";
            $tipoMensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear cuenta | Biblioteca Digital</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
<div class="register-layout">
    <div class="register-card">
        <p class="overline heading-serif">Crea tu cuenta</p>
        <h1 class="main-title heading-serif">Registro</h1>
        <p class="subtitle">
            Crea tu cuenta para acceder a la biblioteca digital y gestionar tus lecturas.
        </p>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $tipoMensaje === 'error' ? 'alert-error' : 'alert-success'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="register-form">
            <div class="field">
                <label for="cip">CIP / Cédula</label>
                <input type="text" id="cip" name="cip"
                       value="<?php echo htmlspecialchars($formData['cip']); ?>">
            </div>

            <div class="field field-inline">
                <div>
                    <label for="primer_nombre">Primer nombre</label>
                    <input type="text" id="primer_nombre" name="primer_nombre"
                           value="<?php echo htmlspecialchars($formData['primer_nombre']); ?>">
                </div>
                <div>
                    <label for="segundo_nombre">Segundo nombre</label>
                    <input type="text" id="segundo_nombre" name="segundo_nombre"
                           value="<?php echo htmlspecialchars($formData['segundo_nombre']); ?>">
                </div>
            </div>

            <div class="field field-inline">
                <div>
                    <label for="primer_apellido">Primer apellido</label>
                    <input type="text" id="primer_apellido" name="primer_apellido"
                           value="<?php echo htmlspecialchars($formData['primer_apellido']); ?>">
                </div>
                <div>
                    <label for="segundo_apellido">Segundo apellido</label>
                    <input type="text" id="segundo_apellido" name="segundo_apellido"
                           value="<?php echo htmlspecialchars($formData['segundo_apellido']); ?>">
                </div>
            </div>

            <div class="field field-inline">
                <div>
                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                           value="<?php echo htmlspecialchars($formData['fecha_nacimiento']); ?>">
                </div>
                <div>
                    <label for="carrera_id">Carrera actual</label>
                    <select id="carrera_id" name="carrera_id">
                        <option value="">Seleccione una carrera</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id']; ?>"
                                <?php echo $formData['carrera_id'] == $carrera['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($carrera['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario"
                       value="<?php echo htmlspecialchars($formData['usuario']); ?>">
            </div>

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($formData['email']); ?>">
            </div>

            <div class="field field-inline">
                <div>
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password">
                </div>
                <div>
                    <label for="password2">Confirmar</label>
                    <input type="password" id="password2" name="password2">
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-primary">
                    Crear cuenta
                </button>
            </div>
        </form>

        <p class="small-text">
            <span class="small-label">¿Ya tienes cuenta?</span>
            <a href="login.php">Inicia sesión</a>
        </p>
    </div>
</div>
</body>
</html>
