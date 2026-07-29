<?php

require_once("sesion.php");
require_once("conexion.php");

// Solo permite solicitudes mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: carrito.php");
    exit();
}

// Recupera y valida los datos recibidos.
$idProducto = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

$cantidad = filter_input(
    INPUT_POST,
    "cantidad",
    FILTER_VALIDATE_INT
);

// Valida que el identificador y la cantidad sean correctos.
if (
    $idProducto === false ||
    $idProducto === null ||
    $cantidad === false ||
    $cantidad === null ||
    $idProducto <= 0 ||
    $cantidad <= 0
) {
    $conn->close();

    header("Location: carrito.php?error=cantidad");
    exit();
}

// Verifica que el producto exista dentro del carrito.
if (
    !isset($_SESSION["carrito"][$idProducto]) ||
    !is_array($_SESSION["carrito"][$idProducto])
) {
    $conn->close();

    header("Location: carrito.php?error=producto");
    exit();
}

// Consulta el stock actual del producto.
$sqlStock = "SELECT stock
             FROM producto
             WHERE id_producto = ?";

$stmtStock = $conn->prepare($sqlStock);

if (!$stmtStock) {
    $conn->close();

    header("Location: carrito.php?error=sistema");
    exit();
}

$stmtStock->bind_param(
    "i",
    $idProducto
);

if (!$stmtStock->execute()) {
    $stmtStock->close();
    $conn->close();

    header("Location: carrito.php?error=sistema");
    exit();
}

$resultadoStock = $stmtStock->get_result();

// Verifica que el producto todavía exista en la base de datos.
if ($resultadoStock->num_rows !== 1) {
    $stmtStock->close();
    $conn->close();

    header("Location: carrito.php?error=producto");
    exit();
}

$producto = $resultadoStock->fetch_assoc();

$stockDisponible = (int) $producto["stock"];

// Verifica que exista stock disponible.
if ($stockDisponible <= 0) {
    $stmtStock->close();
    $conn->close();

    header("Location: carrito.php?error=stock");
    exit();
}

// Verifica que la cantidad solicitada no supere el stock.
if ($cantidad > $stockDisponible) {
    $stmtStock->close();
    $conn->close();

    header("Location: carrito.php?error=stock");
    exit();
}

// Actualiza la cantidad almacenada en la sesión.
$_SESSION["carrito"][$idProducto]["cantidad"] = $cantidad;

$stmtStock->close();
$conn->close();

// Regresa al carrito con un mensaje de confirmación.
header("Location: carrito.php?actualizado=exitoso");
exit();