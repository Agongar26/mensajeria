<?php
session_start(); // Inicia la sesión

// Configuración de conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Usuarios";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

//Comprobar si ocurre algún error a la hora de establecer conexión con la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar la variable para mostrar mensaje en caso de error
$error = "";

// Procesar el formulario solo si se envía una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['alias'])) {
    // Obtener datos del formulario
    $alias = trim($_POST['alias'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $date = trim($_POST['date'] ?? '');

    if ($alias && $password && $name && $apellidos && $date) {
        // Verificar si el alias ya está registrado
        $sql = "SELECT * FROM Usuario WHERE alias = ?"; //Hacer consulta para obtener todos los datos del usuario con el alias especificado
        $stmt = $conn->prepare($sql);   //Preparamos la consulta
        $stmt->bind_param("s", $alias);     //Se asocian los parámetros
        $stmt->execute();   //Se ejecuta la consulta
        $result = $stmt->get_result();      //Obtener el resultado de la consulta

        if ($result->num_rows > 0) {    //Comprobar si existe el alias en la base de datos
            $error = "El alias ya está en uso. Por favor, elige otro.";
        } else {
            // Hashear la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario
            $sql2 = "INSERT INTO Usuario (alias, password, nombre, apellidos, fecha_nacimiento) VALUES (?, ?, ?, ?, ?)";    //Realizar el insert con los datos
            $stmt2 = $conn->prepare($sql2);     //Preparar la consulta

            if ($stmt2) {
                $stmt2->bind_param("sssss", $alias, $hashed_password, $name, $apellidos, $date);    //Asociar los parámetros antes de ejecutar la consulta

                if ($stmt2->execute()) {
                    // Guardar datos en sesión
                    $_SESSION['alias'] = $alias;    //Guardar alias del usuario
                    $_SESSION['nombre'] = $name;    //Guardar nombre del usuario
                    $_SESSION['apellido'] = $apellidos;     //Guardar los apellidos del usuario
                    $_SESSION['fecha_nacimiento'] = $date;  //Guardar la fecha de nacimineto del usuario

                    $error = "Registro exitoso.";
                } else {
                    $error = "Error al registrar al usuario. Inténtalo más tarde.";
                }

                $stmt2->close();    //Cerrar la consulta
            } else {
                $error = "Error en la consulta de inserción. Verifica la base de datos.";
            }
        }
        $stmt->close();     //Cerrar la consulta
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}

$conn->close();     //Cerrar la conexión con la base de datos
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> <!-- bootstrap -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> <!-- ioicon -->
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script> <!-- ioicon -->
    <title>Formulario de Registro</title>
</head>
<body>

    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../img/Logo.jpeg" alt="Logo" style="width:40px;" class="rounded-pill">
            </a>
        </div>
        <div class="d-flex align-items-center justify-content-end w-100">
            <?php if (isset($_SESSION['alias'])): ?>
                <p class="mb-0 text-white" id="User">Bienvenido, <?php echo htmlspecialchars($_SESSION['alias']); ?>!</p>
                <a href="logout.php" class="btn btn-danger ms-2">Cerrar sesión</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <?php if (!isset($_SESSION['alias'])): ?>
            <form id="Formulario" method="post">
                <h1>Formulario de Registro</h1>
                <br>

                <div class="input-group mb-3">
                    <span class="input-group-text">@</span>
                    <input type="text" class="form-control" name="alias" placeholder="Alias" maxlength="15" required>
                </div>

                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Contraseña (mínimo 8 caracteres)" minlength="8" required>
                    <span class="input-group-text"><ion-icon name="lock-closed-outline"></ion-icon></span>
                </div>

                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="name" placeholder="Nombre" minlength="3" maxlength="50" required>
                    <span class="input-group-text"><ion-icon name="person-outline"></ion-icon></span>
                </div>

                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="apellidos" placeholder="Apellidos" minlength="3" maxlength="100" required>
                    <span class="input-group-text"><ion-icon name="person-outline"></ion-icon></span>
                </div>

                <div class="input-group mb-3">
                    <input type="date" class="form-control" name="date" required>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="reset" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </div>

                <div class="input-group justify-content-end">
                    <p>Ya tiene una cuenta? <a href="login.php">Iniciar sesión</a></p>
                </div>

                <?php if (!empty($error)): ?>
                    <p class="text-danger mt-3"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="text-center">
                <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['alias']); ?>!</h1>
                <br>
                <h3>Resumen del formulario</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                <p><strong>Apellidos:</strong> <?php echo htmlspecialchars($_SESSION['apellido']); ?></p>
                <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($_SESSION['fecha_nacimiento']); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p>&copy; 2024 Alejandro González García. Todos los derechos reservados.</p>
            <p>
                <a href="#" class="text-white">Política de Privacidad</a>
                <a href="#" class="text-white">Términos de Servicio</a>
            </p>
            <p>
                <a href="https://www.instagram.com/"><ion-icon name="logo-instagram"></ion-icon></a>
                <a href="https://www.facebook.com/"><ion-icon name="logo-facebook"></ion-icon></a>
                <a href="https://x.com/"><ion-icon name="logo-twitter"></ion-icon></a>
            </p>
        </div>
    </footer>

</body>
</html>