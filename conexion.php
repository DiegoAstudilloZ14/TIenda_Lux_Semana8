<?php

// Datos de conexión a MySQL.
$servidor = "localhost";
$usuario = "root";
$password = "";
$baseDatos = "tienda";

// Crea la conexión.
$conn = new mysqli(
    $servidor,
    $usuario,
    $password,
    $baseDatos
);

// Verifica la conexión.
if ($conn->connect_error) {
    die(
        "No fue posible establecer la conexión " .
        "con la base de datos."
    );
}

// Configura la codificación UTF-8.
if (!$conn->set_charset("utf8mb4")) {
    die(
        "No fue posible configurar la codificación " .
        "de la base de datos."
    );
}