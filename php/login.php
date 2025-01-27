<?php
session_start(); // Inicia la sesión

// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mensajeriaweb"; // Nombre de la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si hay algún error en la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['alias'])) {
    header("Location: index.php"); // Redirigir al index si está autenticado
    exit();
}

// Inicializamos la variable $error
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener alias y contraseña del formulario
    $alias = $_POST['alias'];
    $password = $_POST['password'];

    // Verificar si el alias existe en la base de datos
    $sql = "SELECT * FROM Usuario WHERE alias = ?"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $alias);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { // Si el alias existe
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) { // Verificar la contraseña
            $_SESSION['alias'] = $alias;
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['apellido'] = $row['apellidos'];
            $_SESSION['fecha_nacimiento'] = $row['fecha_nacimiento'];
            header("Location: index.php");
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
<html lang="es">
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