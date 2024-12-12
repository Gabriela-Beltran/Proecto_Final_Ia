<?php
    include 'connection.php';
        
    $name = $_POST['name'];
    $age = $_POST['age'];
    $tel = $_POST['tel'];
    $pass = $_POST['pass'];
    $user = $_POST['user'];

    $user_exist = "SELECT Tel FROM usuarios WHERE Tel = '$tel'";
    $exist = $connection->prepare($user_exist);
    $exist->execute();
    $response = $exist->fetchAll();
    if(!empty($response)) {
        setcookie('msj_registro', 'El telefono ya existe, usa otro', time() + 2);
    } else {
        $new_user = "INSERT INTO usuarios (Nombre, Edad, Tel, Usuario, Contrasena, Estatus) VALUES ('$name', '$age', '$tel', '$user', '$pass', 1)";
        $register = $connection->prepare($new_user);
        $register->execute();
        
       
        setcookie('msj_registro', 'El usuario se ha registrado', time() + 2);
    }
    
    header("Location: register.php");
?>
