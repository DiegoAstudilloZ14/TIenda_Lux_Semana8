<?php

require_once("sesion.php");

// Solo permite solicitudes mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: carrito.php");
    exit();
}

// Recupera y valida el identificador del producto.
$idProducto = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

// Verifica que el identificador sea válido.
if (
    $idProducto === false ||
    $idProducto === null ||
    $idProducto <= 0
) {
    header("Location: carrito.php?error=producto");
    exit();
}

// Verifica que el producto exista en el carrito.
if (
    !isset($_SESSION["carrito"][$idProducto]) ||
    !is_array($_SESSION["carrito"][$idProducto])
) {
    header("Location: carrito.php?error=producto");
    exit();
}

// Elimina el producto del carrito.
unset($_SESSION["carrito"][$idProducto]);

// Regresa al carrito.
header("Location: carrito.php?eliminado=exitoso");
exit();