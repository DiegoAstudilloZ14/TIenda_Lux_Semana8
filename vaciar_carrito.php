<?php

require_once("sesion.php");

// Solo permite solicitudes mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: carrito.php");
    exit();
}

// Vacía completamente el carrito.
$_SESSION["carrito"] = [];

// Regresa al carrito.
header("Location: carrito.php?vaciado=exitoso");
exit();