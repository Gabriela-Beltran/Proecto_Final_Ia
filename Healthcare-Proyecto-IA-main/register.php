<?php
    if (isset($_COOKIE['msj_registro'])) {
        $msj = $_COOKIE['msj_registro'];
        setcookie('msj_registro', '', time() - 1);
        echo "<h3>$msj</h3>";
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE-edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro</title>
        <link rel="stylesheet" href="css/registro.css">
    </head>
    <body>
        <header>
        <?php
            $_GET['nombreUsuario'] = '';
            include("header.php"); 
        ?>
        </header>
        <main>
            <div class="contenedor" >
                <h2>Registro</h2>
                <form method="POST" action="registrar.php">
                    <div class="fila1">
                     <div class="campo">
                        <label for="nombre">Nombre:</label>
                        <input type="text"  name="name" placeholder="Juanito">
                    </div>

                    <div class="campo">
                        <label for="edad">Edad:</label>
                        <input type="number" name="age" placeholder="18 - 100">
                    </div>

                    </div>


                    <div class="campo">
                        <label for="tel">Telefono</label>
                        <input type="number" name="tel" placeholder="6681889076">
                    </div>
                    <div class="fila1">

                    <div class="campo">
                        <label for="usuario">Usuario:</label>
                        <input type="text" name="user" placeholder="user123">
                    </div>
                    
                    <div class="campo">
                        <label for="pass">Contraseña:</label>
                        <input type="password"  name="pass"  placeholder="MiContrasena123$%&">
                    </div>

                    </div>


                    <button type="submit" name="registrar">Registrar</button>
                </form>


                <form method="POST" action="login.php">
                    <button  type="submit" name="login">Ir iniciar Sesión</button>
                </form>
            </div>
        </main>
        <footer class="footer">
            <?php include("footer.php"); ?>
        </footer>
    </body>
</html>