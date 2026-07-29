<?php

require_once("sesion.php");
require_once("conexion.php");

// Solo acepta solicitudes POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// Recupera y valida el identificador del producto.
$idProducto = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (
    $idProducto === false ||
    $idProducto === null ||
    $idProducto <= 0
) {
    $conn->close();

    header("Location: index.php?error=producto");
    exit();
}

// Consulta preparada para recuperar el producto.
$sqlProducto = "SELECT
                    id_producto,
                    nombre,
                    precio,
                    stock
                FROM producto
                WHERE id_producto = ?";

$stmtProducto = $conn->prepare($sqlProducto);

if (!$stmtProducto) {
    $conn->close();

    header("Location: index.php?error=sistema");
    exit();
}

$stmtProducto->bind_param(
    "i",
    $idProducto
);

if (!$stmtProducto->execute()) {
    $stmtProducto->close();
    $conn->close();

    header("Location: index.php?error=sistema");
    exit();
}

$resultadoProducto = $stmtProducto->get_result();

if ($resultadoProducto->num_rows !== 1) {
    $stmtProducto->close();
    $conn->close();

    header("Location: index.php?error=producto");
    exit();
}

$producto = $resultadoProducto->fetch_assoc();

$stockDisponible = (int) $producto["stock"];

// Verifica que exista stock.
if ($stockDisponible <= 0) {
    $stmtProducto->close();
    $conn->close();

    header("Location: index.php?error=stock");
    exit();
}

// Cantidad actual en el carrito.
$cantidadActual = 0;

if (isset($_SESSION["carrito"][$idProducto])) {
    $cantidadActual =
        (int) $_SESSION["carrito"][$idProducto]["cantidad"];
}

// Impide superar el stock.
if ($cantidadActual >= $stockDisponible) {
    $stmtProducto->close();
    $conn->close();

    header("Location: index.php?error=stock");
    exit();
}

// Agrega el producto o incrementa la cantidad.
if (isset($_SESSION["carrito"][$idProducto])) {

    $_SESSION["carrito"][$idProducto]["cantidad"]++;

} else {

    $_SESSION["carrito"][$idProducto] = [

        "nombre" => $producto["nombre"],

        "precio" => (float) $producto["precio"],

        "cantidad" => 1

    ];
}

$stmtProducto->close();
$conn->close();

header("Location: carrito.php");
exit();