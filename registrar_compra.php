<?php
session_start();
require_once("conexion.php");

// Solo se permite el acceso mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: compras.php");
    exit();
}

// Recupera y valida los datos enviados.
$idCliente = filter_input(
    INPUT_POST,
    "id_cliente",
    FILTER_VALIDATE_INT
);

$idProducto = filter_input(
    INPUT_POST,
    "id_producto",
    FILTER_VALIDATE_INT
);

$cantidad = filter_input(
    INPUT_POST,
    "cantidad",
    FILTER_VALIDATE_INT
);

// Validación básica de los datos.
if (
    $idCliente === false ||
    $idProducto === false ||
    $cantidad === false ||
    $idCliente <= 0 ||
    $idProducto <= 0 ||
    $cantidad <= 0
) {
    die(
        "Los datos ingresados para registrar la compra " .
        "no son válidos."
    );
}

$stmtProducto = null;
$stmtCliente = null;
$stmtCompra = null;
$stmtStock = null;

// Inicia una transacción.
$conn->begin_transaction();

try {
    // Recupera y bloquea el producto seleccionado.
    $sqlProducto = "SELECT nombre, precio, stock
                    FROM producto
                    WHERE id_producto = ?
                    FOR UPDATE";

    $stmtProducto = $conn->prepare($sqlProducto);

    if (!$stmtProducto) {
        throw new Exception(
            "No fue posible preparar la consulta del producto."
        );
    }

    $stmtProducto->bind_param("i", $idProducto);
    $stmtProducto->execute();

    $resultadoProducto = $stmtProducto->get_result();

    if ($resultadoProducto->num_rows !== 1) {
        throw new Exception(
            "El producto seleccionado no existe."
        );
    }

    $producto = $resultadoProducto->fetch_assoc();

    // Verifica la disponibilidad.
    if ($cantidad > $producto["stock"]) {
        throw new Exception(
            "La cantidad solicitada supera el stock disponible."
        );
    }

    // Recupera el cliente seleccionado.
    $sqlCliente = "SELECT nombre
                   FROM cliente
                   WHERE id_cliente = ?";

    $stmtCliente = $conn->prepare($sqlCliente);

    if (!$stmtCliente) {
        throw new Exception(
            "No fue posible preparar la consulta del cliente."
        );
    }

    $stmtCliente->bind_param("i", $idCliente);
    $stmtCliente->execute();

    $resultadoCliente = $stmtCliente->get_result();

    if ($resultadoCliente->num_rows !== 1) {
        throw new Exception(
            "El cliente seleccionado no existe."
        );
    }

    $cliente = $resultadoCliente->fetch_assoc();

    // Calcula el total en el servidor.
    $total = $producto["precio"] * $cantidad;

    // Registra la compra.
    $sqlCompra = "INSERT INTO compra
                    (
                        cantidad,
                        total,
                        fecha,
                        id_producto,
                        id_cliente
                    )
                  VALUES (?, ?, CURDATE(), ?, ?)";

    $stmtCompra = $conn->prepare($sqlCompra);

    if (!$stmtCompra) {
        throw new Exception(
            "No fue posible preparar el registro de la compra."
        );
    }

    $stmtCompra->bind_param(
        "idii",
        $cantidad,
        $total,
        $idProducto,
        $idCliente
    );

    if (!$stmtCompra->execute()) {
        throw new Exception(
            "No fue posible registrar la compra."
        );
    }

    // Descuenta el stock del producto.
    $sqlStock = "UPDATE producto
                 SET stock = stock - ?
                 WHERE id_producto = ?";

    $stmtStock = $conn->prepare($sqlStock);

    if (!$stmtStock) {
        throw new Exception(
            "No fue posible preparar la actualización del stock."
        );
    }

    $stmtStock->bind_param(
        "ii",
        $cantidad,
        $idProducto
    );

    if (!$stmtStock->execute()) {
        throw new Exception(
            "No fue posible actualizar el stock."
        );
    }

    if ($stmtStock->affected_rows !== 1) {
        throw new Exception(
            "No se actualizó correctamente el stock."
        );
    }

    // Confirma todas las operaciones.
    $conn->commit();

    // Guarda el cliente seleccionado en la sesión.
    $_SESSION["usuario"] = $cliente["nombre"];

    $stmtProducto->close();
    $stmtCliente->close();
    $stmtCompra->close();
    $stmtStock->close();
    $conn->close();

    header("Location: compras.php?registro=exitoso");
    exit();
} catch (Exception $e) {
    // Deshace todas las operaciones si algo falla.
    $conn->rollback();

    if ($stmtProducto) {
        $stmtProducto->close();
    }

    if ($stmtCliente) {
        $stmtCliente->close();
    }

    if ($stmtCompra) {
        $stmtCompra->close();
    }

    if ($stmtStock) {
        $stmtStock->close();
    }

    $mensajeError = $e->getMessage();

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Error al registrar compra</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

<section class="review-result">
    <h2>No fue posible registrar la compra</h2>

    <p>
        <?php echo htmlspecialchars($mensajeError); ?>
    </p>

    <a href="compras.php">
        Volver al registro de compras
    </a>
</section>

</body>
</html>