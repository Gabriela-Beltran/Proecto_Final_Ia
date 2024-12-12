<?php
try {
    $connection = new PDO('mysql:host=localhost; dbname=proyectoia', 'root', 'root');
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>