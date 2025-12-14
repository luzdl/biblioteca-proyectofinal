<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/User.php';

$db = (new Database())->getConnection();

$carrerasStmt = $db->query("SELECT id, nombre FROM carreras ORDER BY nombre");
$carreras = $carrerasStmt->fetchAll();

$mensaje = "";
$tipoMensaje = "";

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
    foreach ($formData as $key => $_) {
        $formData[$key] = trim($_POST[$key] ?? '');
    }

    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    $camposObligatorios = [
        'cip', 'primer_nombre', 'primer_apellido',
        'fecha_nacimiento', 'carrera_id', 'usuario', 'email'
    ];

    $hayVacios = false;
    foreach ($camposObligatorios as $campo) {
        if ($formData[$campo] === '') {
            $hayVacios = true;
            break;
        }
    }

    if ($hayVacios || $password === '' || $password2 === '') {
        $mensaje = "Debe llenar los campos requeridos.";
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

                foreach ($formData as $key => $_) {
                    $formData[$key] = '';
                }
            } else {
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .field label.req {
        display: inline-flex;
        align-items: center;
        gap: 4px; /* separa el texto del asterisco */
        }

        .field label.req::after {
        content: " *";
        color: var(--terracotta, #C7764A);
        font-weight: 700;
        letter-spacing: 0 !important;   /* evita separación rara */
        margin-left: 4px;
        }

        .is-invalid {
        border-bottom-color: #c0392b !important;
        }
        @keyframes shake {
        20%, 60% { transform: translateX(-4px); }
        40%, 80% { transform: translateX(4px); }
        }
        .shake { animation: shake 0.4s ease; }
        .error-hint {
        font-size: 12px;
        color: #c0392b;
        display: none;
        }
        .error-hint.show { display: block; }
        .field select {
        width: 100%;
        border: none;
        border-bottom: 1px solid rgba(87, 71, 55, 0.45);
        padding: 7px 0 9px;
        background: transparent;
        color: var(--coffee);
        }
        .field select:focus {
        border-bottom-color: var(--sage);
        outline: none;
        }

      /* Ajuste de la lista de SweetAlert2 */
        .swal2-html-container ul {
        padding-left: 18px !important; /* acercar bullets */
        margin: 6px 0 !important;
        text-align: left !important;
        }
        .swal2-html-container ul li {
        margin-left: 0 !important;
        padding-left: 4px !important;
        }
    </style>

    <script>
        const LABELS = {
        cip: "CIP / Cédula",
        primer_nombre: "Primer nombre",
        primer_apellido: "Primer apellido",
        fecha_nacimiento: "Fecha de nacimiento",
        carrera_id: "Carrera actual",
        usuario: "Usuario",
        email: "Correo electrónico",
        password: "Contraseña",
        password2: "Confirmar contraseña"
        };

        function setInvalid(input, show = true) {
        if (!input) return;
        input.classList.toggle('is-invalid', show);
        const hint = input.closest('.field')?.querySelector('.error-hint');
        if (hint) hint.classList.toggle('show', show);
        }

        function clearAllInvalid(ids) {
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) setInvalid(el, false);
        });
        }

        function validarFormulario(e) {
        const card = document.querySelector('.register-card');

        const campos = [
            "cip","primer_nombre","primer_apellido",
            "fecha_nacimiento","carrera_id","usuario","email",
            "password","password2"
        ];

        clearAllInvalid(campos);

        const faltantes = [];
        for (let id of campos) {
            const input = document.getElementById(id);
            if (!input) continue;

            let val = input.value.trim();
            if (input.tagName.toLowerCase() === 'select') val = input.value;

            if (val === "") {
            faltantes.push(id);
            setInvalid(input, true);
            }
        }

        const email = document.getElementById('email')?.value.trim();
        const pass  = document.getElementById('password')?.value;
        const pass2 = document.getElementById('password2')?.value;

        let emailInvalido = false;
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailInvalido = true;
            setInvalid(document.getElementById('email'), true);
        }

        let passNoCoinciden = false;
        if (pass && pass2 && pass !== pass2) {
            passNoCoinciden = true;
            setInvalid(document.getElementById('password'), true);
            setInvalid(document.getElementById('password2'), true);
        }

        if (faltantes.length || emailInvalido || passNoCoinciden) {
            e.preventDefault();

            let items = "";
            if (emailInvalido) items += "<br>Ingresa un correo electrónico válido.";
            if (passNoCoinciden) items += "<br>Las contraseñas no coinciden.";

            Swal.fire({
            icon: 'warning',
            title: 'Faltan campos obligatorios',
            html: items,
            confirmButtonColor: '#C7764A'
            });

            card.classList.remove('shake'); void card.offsetWidth;
            card.classList.add('shake');

            return false;
        }

        return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
        const ids = ["cip","primer_nombre","primer_apellido","fecha_nacimiento","carrera_id","usuario","email","password","password2"];
        ids.forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            input.addEventListener('input', () => setInvalid(input, false));
            input.addEventListener('change', () => setInvalid(input, false));
        });
        });
    </script>
</head>
<body>
<div class="register-layout">
    <div class="register-card">
        <p class="overline heading-serif">Crea tu cuenta</p>
        <h1 class="main-title heading-serif">Registro</h1>
        <p class="subtitle">Crea tu cuenta para acceder a la biblioteca digital y gestionar tus lecturas.</p>

        <?php if ($mensaje): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $tipoMensaje === "error" ? "error" : "success"; ?>',
                    title: '<?php echo $tipoMensaje === "error" ? "Error" : "Éxito"; ?>',
                    text: '<?php echo htmlspecialchars($mensaje); ?>',
                    confirmButtonColor: '#C7764A'
                });
            </script>
        <?php endif; ?>

        <form id="register-form" method="POST" action="" class="register-form" onsubmit="validarFormulario(event)">
            
            <div class="field">
                <label for="cip" class="req">CIP / Cédula</label>
                <input type="text" id="cip" name="cip" value="<?php echo htmlspecialchars($formData['cip']); ?>">
                <div class="error-hint">Este campo es obligatorio.</div>
            </div>

            <div class="field field-inline">
                <div class="field">
                    <label for="primer_nombre" class="req">Primer nombre</label>
                    <input type="text" id="primer_nombre" name="primer_nombre" value="<?php echo htmlspecialchars($formData['primer_nombre']); ?>">
                    <div class="error-hint">Este campo es obligatorio.</div>
                </div>
                <div class="field">
                    <label for="segundo_nombre">Segundo nombre</label>
                    <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?php echo htmlspecialchars($formData['segundo_nombre']); ?>">
                </div>
            </div>

            <div class="field field-inline">
                <div class="field">
                    <label for="primer_apellido" class="req">Primer apellido</label>
                    <input type="text" id="primer_apellido" name="primer_apellido" value="<?php echo htmlspecialchars($formData['primer_apellido']); ?>">
                    <div class="error-hint">Este campo es obligatorio.</div>
                </div>
                <div class="field">
                    <label for="segundo_apellido">Segundo apellido</label>
                    <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?php echo htmlspecialchars($formData['segundo_apellido']); ?>">
                </div>
            </div>

            <div class="field field-inline">
                <div class="field">
                    <label for="fecha_nacimiento" class="req">Fecha de nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($formData['fecha_nacimiento']); ?>">
                    <div class="error-hint">Este campo es obligatorio.</div>
                </div>
                <div class="field">
                    <label for="carrera_id" class="req">Carrera actual</label>
                    <select id="carrera_id" name="carrera_id">
                        <option value="">Seleccione una carrera</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id']; ?>" <?php echo $formData['carrera_id'] == $carrera['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($carrera['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-hint">Este campo es obligatorio.</div>
                </div>
            </div>

            <div class="field">
                <label for="usuario" class="req">Usuario</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($formData['usuario']); ?>">
                <div class="error-hint">Este campo es obligatorio.</div>
            </div>

            <div class="field">
                <label for="email" class="req">Correo electrónico</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>">
                <div class="error-hint">Ingresa un correo válido.</div>
            </div>

            <div class="field field-inline">
                <div class="field">
                    <label for="password" class="req">Contraseña</label>
                    <input type="password" id="password" name="password">
                    <div class="error-hint">Este campo es obligatorio.</div>
                </div>
                <div class="field">
                    <label for="password2" class="req">Confirmar contraseña</label>
                    <input type="password" id="password2" name="password2">
                    <div class="error-hint">Repite la contraseña.</div>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-primary">Crear cuenta</button>
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
