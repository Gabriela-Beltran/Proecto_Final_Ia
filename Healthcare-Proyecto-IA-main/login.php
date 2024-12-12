<?php
        if (isset($_COOKIE['mensaje'])) {
            $msj = $_COOKIE['mensaje'];
            setcookie('mensaje', '', time() - 1);
            echo "<h3>$msj</h3>";
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<header>
        <?php
            $_GET['nombreUsuario'] = '';
            include("header.php"); 
        ?>
        <div style="clear: both;"></div>
    </header>
<main>
    <div class="contenedor">
    <h2>Iniciar sesión</h2>
        <form action="check.php" method="post" class="formulario" id="formulario">

            <div class="campo">
            <label for="usuario" >Usuario:</label>
            <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario" class="input" >
            </div>

            <div class="campo">
            <label for="password">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña" class="input" >
            </div>

            <div class="enlaces">
          <p>¿Aun no tienes cuenta?</p>  
          <a class="enlace-btn" href="register.php">Registrate</a>
      
          </div>
        <div class="botones">
          <button type="submit" name="submit" value="Iniciar sesión" class="btn">Iniciar sesion</button>
          <button type="reset" name="reset">Borrar campos</button>
        </form>
    </div>
</main>
<footer>
    <?php include("footer.php"); ?>   
</footer>
</body>
</html>