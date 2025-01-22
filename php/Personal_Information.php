<?php
session_start(); // Inicia o reanuda la sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['alias'])) {
    // Redirigir a la página de inicio de sesión si no está autenticado
    header("Location: login.php");
    exit();
}

// Variables del usuario obtenidas de la sesión
$alias = htmlspecialchars($_SESSION['alias']);
$nombre = htmlspecialchars($_SESSION['nombre']);
$apellido = htmlspecialchars($_SESSION['apellido']);
$fecha_nacimiento = htmlspecialchars($_SESSION['fecha_nacimiento']);
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
        <div class="d-flex align-items-center justify-content-left">
            <img src="../img/Logo.jpeg" alt="Logo" style="width:40px;" class="rounded-pill">
            <p class="mb-0"><a class="text-white text-decoration-none" href="index.php">Volver</a></p>
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

    <div class="offcanvas offcanvas-top" id="demo">
        <div class="offcanvas-header">
            <h1 class="offcanvas-title">¿Está seguro que desea eliminar la cuenta?</h1>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <p>Esta acción no se puede deshacer</p>
            <button class="btn btn-success" type="button">Si</button>
            <button class="btn btn-danger" type="button" data-bs-dismiss="offcanvas">No</button>
        </div>
    </div>

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="text-center">
            <h1>Usuario: <?php echo htmlspecialchars($_SESSION['alias']); ?></h1>
            <br>
            <h2>Tu información</h2>
            <p><strong>Nombre: </strong><?php echo htmlspecialchars($_SESSION['nombre']);?></p>
            <p><strong>Apellidos: </strong><?php echo htmlspecialchars($_SESSION['apellido']);?></p>
            <p><strong>Fecha de nacimiento: </strong><?php echo htmlspecialchars($_SESSION['fecha_nacimiento']);?></p>
            <button class="btn btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#demo">
                Eliminar cuenta
            </button>
        </div>
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