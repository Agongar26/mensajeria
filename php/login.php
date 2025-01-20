<?php
session_start(); //Inicia la sesión

//Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Usuarios";
$conn = new mysqli($servername, $username, $password, $dbname);

//Comprobar si ocurre algún error a la hora de establecer conexión con la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

//Inicializamos la variable $error para evitar posibles errores en caso de que no se haya definido antes.
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Obtener alias y contraseña del formulario
    $alias = $_POST['alias'];
    $password = $_POST['password'];

    //Verificar si el alias existe en la base de datos
    $sql = "SELECT * FROM Usuario WHERE alias = ?"; //Hacer consulta para obtener todos los datos del usuario con el alias especificado
    $stmt = $conn->prepare($sql);   //Preparamos la consulta
    $stmt->bind_param("s", $alias);     //Se asocian los parámetros
    $stmt->execute();   //Se ejecuta la consulta
    $result = $stmt->get_result();      //Obtener el resultado de la consulta

    if ($result->num_rows > 0) {    //Comprobar que exista registro en la base de datos con los datos especificados
        // Verificar la contraseña
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            //Si las credenciales son correctas, iniciar la sesión
            $_SESSION['alias'] = $alias;  //Guardamos el alias del usuario
            $_SESSION['nombre'] = $row['nombre'];   //Guardamos el nombre del usuario
            $_SESSION['apellido'] = $row['apellidos'];  //Guardamos los apellidos del usuario  
            $_SESSION['fecha_nacimiento'] = $row['fecha_nacimiento'];   //Guardamos la fecha de nacimiento del usuario
            header("Location: index.php");  //Redirigir a la página principal después del inicio de sesión exitoso
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } else {
        $error = "Usuario o contraseña incorrectos";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}
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
    <title>Formulario de inicio de sesión</title>
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
                <p class="mb-0 text-white" id="User">Bienvenido, <?php echo htmlspecialchars($_SESSION['alias']);?>!</p>
                <a href="logout.php" class="btn btn-danger" style="margin-left: 10px; margin-right: 5px;">Cerrar sesión</a>
            <?php else: ?>
                <p class="mb-0 text-white" id="User"></p>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-center vh-100" id="Validation">
        <?php// if (!isset($_SESSION['alias'])): ?>
            <form id="Formulario" method="post">
                <h1>Iniciar de sesión</h1>
                <br>

                <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="text" class="form-control" name="alias" id="alias" placeholder="Alias" maxlength="15" required>
                </div>

                <div class="input-group">
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                    <span class="input-group-text"><ion-icon name="lock-closed-outline"></ion-icon></span>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="reset" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </div>

                <div class="input-group justify-content-end">
                    <p>No tiene cuenta? <a href="registro.php">Registrarse</a></p>
                </div>
                <?php if (!empty($error)): ?>
                    <p id="mensajeValidacion" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        <?//php else: ?>
            <div class="text-center">
                <h1>Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['alias']); ?></h1>
                <br>
                <h2>Tu información</h2>
                <p><strong>Nombre: </strong><?php echo htmlspecialchars($_SESSION['nombre']);?></p>
                <p><strong>Apellidos: </strong><?php echo htmlspecialchars($_SESSION['apellido']);?></p>
                <p><strong>Fecha de nacimiento: </strong><?php echo htmlspecialchars($_SESSION['fecha_nacimiento']);?></p>
            </div>
        <?//php endif; ?>
    </div>

    <div>
        <button><a href="index.php">Ir a mensajes</a></button>
    </div>

    <!-- Muestra la pantalla de mensajería pero hace falta arreglarlo <div>
        <?php include 'index.php' ?>
    </div>-->

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