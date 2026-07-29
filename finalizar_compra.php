<?php

require_once("sesion.php");
require_once("conexion.php");

// Mensaje temporal mostrado después de procesar la compra.
$mensajeCheckout = $_SESSION["mensaje_checkout"] ?? null;
$tipoMensaje = $_SESSION["tipo_mensaje_checkout"] ?? null;

unset(
    $_SESSION["mensaje_checkout"],
    $_SESSION["tipo_mensaje_checkout"]
);

// Genera un token para proteger el formulario.
if (
    !isset($_SESSION["token_checkout"]) ||
    !is_string($_SESSION["token_checkout"])
) {
    $_SESSION["token_checkout"] = bin2hex(
        random_bytes(32)
    );
}

/*
Procesamiento de la compra.
Esta sección solamente se ejecuta cuando se envía
el formulario mediante POST.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $idCliente = filter_input(
        INPUT_POST,
        "id_cliente",
        FILTER_VALIDATE_INT
    );

    $tokenRecibido = $_POST["token_checkout"] ?? "";

    // Verifica el token del formulario.
    if (
        !is_string($tokenRecibido) ||
        !hash_equals(
            $_SESSION["token_checkout"],
            $tokenRecibido
        )
    ) {
        $_SESSION["mensaje_checkout"] =
            "La solicitud no es válida. Intente nuevamente.";

        $_SESSION["tipo_mensaje_checkout"] = "error";

        header("Location: finalizar_compra.php");
        exit();
    }

    // Verifica que el cliente sea válido.
    if (
        $idCliente === false ||
        $idCliente === null ||
        $idCliente <= 0
    ) {
        $_SESSION["mensaje_checkout"] =
            "Debe seleccionar un cliente válido.";

        $_SESSION["tipo_mensaje_checkout"] = "error";

        header("Location: finalizar_compra.php");
        exit();
    }

    // Verifica que existan productos en el carrito.
    if (empty($_SESSION["carrito"])) {
        $_SESSION["mensaje_checkout"] =
            "No existen productos para registrar la compra.";

        $_SESSION["tipo_mensaje_checkout"] = "error";

        header("Location: finalizar_compra.php");
        exit();
    }

    try {
        // Inicia la transacción.
        if (!$conn->begin_transaction()) {
            throw new RuntimeException(
                "No fue posible iniciar la transacción."
            );
        }

        /*
        Verifica que el cliente seleccionado exista.
        */
        $sqlCliente = "SELECT nombre
                       FROM cliente
                       WHERE id_cliente = ?";

        $stmtCliente = $conn->prepare($sqlCliente);

        if (!$stmtCliente) {
            throw new RuntimeException(
                "No fue posible verificar el cliente."
            );
        }

        $stmtCliente->bind_param(
            "i",
            $idCliente
        );

        if (!$stmtCliente->execute()) {
            throw new RuntimeException(
                "No fue posible consultar el cliente."
            );
        }

        $resultadoCliente = $stmtCliente->get_result();

        if ($resultadoCliente->num_rows !== 1) {
            throw new RuntimeException(
                "El cliente seleccionado no existe."
            );
        }

        $cliente = $resultadoCliente->fetch_assoc();
        $nombreCliente = $cliente["nombre"];

        $stmtCliente->close();

        /*
        Consulta cada producto y bloquea su fila mientras
        se procesa la compra.

        FOR UPDATE evita que otra operación modifique el stock
        hasta que la transacción termine.
        */
        $sqlProducto = "SELECT
                            nombre,
                            precio,
                            stock
                        FROM producto
                        WHERE id_producto = ?
                        FOR UPDATE";

        $stmtProducto = $conn->prepare($sqlProducto);

        if (!$stmtProducto) {
            throw new RuntimeException(
                "No fue posible preparar la consulta de productos."
            );
        }

        /*
        Registra una fila en la tabla compra por cada producto
        almacenado en el carrito.
        */
        $sqlCompra = "INSERT INTO compra (
                          cantidad,
                          total,
                          fecha,
                          id_producto,
                          id_cliente
                      )
                      VALUES (?, ?, CURDATE(), ?, ?)";

        $stmtCompra = $conn->prepare($sqlCompra);

        if (!$stmtCompra) {
            throw new RuntimeException(
                "No fue posible preparar el registro de compras."
            );
        }

        /*
        Descuenta del inventario la cantidad comprada.
        */
        $sqlActualizarStock = "UPDATE producto
                               SET stock = stock - ?
                               WHERE id_producto = ?";

        $stmtActualizarStock = $conn->prepare(
            $sqlActualizarStock
        );

        if (!$stmtActualizarStock) {
            throw new RuntimeException(
                "No fue posible preparar la actualización del stock."
            );
        }

        $totalGeneral = 0;

        foreach (
            $_SESSION["carrito"] as $idProducto => $itemCarrito
        ) {
            $idProducto = (int) $idProducto;
            $cantidad = (int) (
                $itemCarrito["cantidad"] ?? 0
            );

            if ($idProducto <= 0 || $cantidad <= 0) {
                throw new RuntimeException(
                    "El carrito contiene información inválida."
                );
            }

            /*
            Recupera el precio y el stock directamente desde MySQL.

            No se utiliza el precio almacenado en la sesión para
            registrar la compra.
            */
            $stmtProducto->bind_param(
                "i",
                $idProducto
            );

            if (!$stmtProducto->execute()) {
                throw new RuntimeException(
                    "No fue posible verificar uno de los productos."
                );
            }

            $resultadoProducto = $stmtProducto->get_result();

            if ($resultadoProducto->num_rows !== 1) {
                throw new RuntimeException(
                    "Uno de los productos ya no existe."
                );
            }

            $producto = $resultadoProducto->fetch_assoc();

            $precioActual = (float) $producto["precio"];
            $stockActual = (int) $producto["stock"];
            $nombreProducto = $producto["nombre"];

            if ($cantidad > $stockActual) {
                throw new RuntimeException(
                    "No existe stock suficiente para " .
                    $nombreProducto . "."
                );
            }

            $subtotal = $precioActual * $cantidad;
            $totalGeneral += $subtotal;

            // Registra la compra del producto.
            $stmtCompra->bind_param(
                "idii",
                $cantidad,
                $subtotal,
                $idProducto,
                $idCliente
            );

            if (!$stmtCompra->execute()) {
                throw new RuntimeException(
                    "No fue posible registrar uno de los productos."
                );
            }

            // Descuenta el stock.
            $stmtActualizarStock->bind_param(
                "ii",
                $cantidad,
                $idProducto
            );

            if (!$stmtActualizarStock->execute()) {
                throw new RuntimeException(
                    "No fue posible actualizar el inventario."
                );
            }

            if ($stmtActualizarStock->affected_rows !== 1) {
                throw new RuntimeException(
                    "No fue posible descontar el stock del producto."
                );
            }
        }

        $stmtProducto->close();
        $stmtCompra->close();
        $stmtActualizarStock->close();

        // Confirma todos los cambios realizados.
        if (!$conn->commit()) {
            throw new RuntimeException(
                "No fue posible confirmar la compra."
            );
        }

        // Actualiza el nombre visible en la sesión.
        $_SESSION["usuario"] = $nombreCliente;

        // Vacía el carrito después de confirmar la transacción.
        $_SESSION["carrito"] = [];

        // Renueva el token para impedir reutilizar el formulario.
        $_SESSION["token_checkout"] = bin2hex(
            random_bytes(32)
        );

        $_SESSION["mensaje_checkout"] =
            "Compra registrada correctamente. Total: $" .
            number_format(
                $totalGeneral,
                0,
                ",",
                "."
            ) .
            ".";

        $_SESSION["tipo_mensaje_checkout"] = "exito";

        $conn->close();

        header("Location: finalizar_compra.php");
        exit();

    } catch (Throwable $error) {
        // Revierte todos los cambios si alguna operación falla.
        $conn->rollback();

        $_SESSION["mensaje_checkout"] =
            $error->getMessage();

        $_SESSION["tipo_mensaje_checkout"] = "error";

        $conn->close();

        header("Location: finalizar_compra.php");
        exit();
    }
}

/*
Recupera los clientes disponibles para mostrarlos
en el formulario de confirmación.
*/
$sqlClientes = "SELECT
                    id_cliente,
                    nombre,
                    email
                FROM cliente
                ORDER BY nombre ASC";

$resultadoClientes = $conn->query($sqlClientes);

if (!$resultadoClientes) {
    $conn->close();

    die(
        "No fue posible recuperar los clientes registrados."
    );
}

$hayClientes = $resultadoClientes->num_rows > 0;

// Calcula el total estimado mostrado en pantalla.
$totalEstimado = 0;

foreach ($_SESSION["carrito"] as $itemCarrito) {
    $precio = (float) (
        $itemCarrito["precio"] ?? 0
    );

    $cantidad = (int) (
        $itemCarrito["cantidad"] ?? 0
    );

    $totalEstimado += $precio * $cantidad;
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

    <title>Finalizar compra</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="encabezado-tienda">
    <h1>Finalizar compra</h1>

    <p>
        Revisa el pedido y selecciona al cliente
        antes de confirmar la operación.
    </p>
</section>

<?php if ($mensajeCheckout !== null): ?>

    <section class="review-section">

        <div class="<?php
        echo $tipoMensaje === "exito"
            ? "mensaje-exito"
            : "mensaje-error";
        ?>">
            <?php
            echo htmlspecialchars(
                $mensajeCheckout,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>
        </div>

        <?php if ($tipoMensaje === "exito"): ?>

            <div class="acciones-inferiores">
                <a
                    href="index.php"
                    class="boton-pedido"
                >
                    Volver a la tienda
                </a>

                <a
                    href="clientes_frecuentes.php"
                    class="boton-pedido boton-secundario"
                >
                    Ver resumen de compras
                </a>
            </div>

        <?php endif; ?>

    </section>

<?php endif; ?>

<?php if (!empty($_SESSION["carrito"])): ?>

    <section class="review-section">
        <h2>Resumen del pedido</h2>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach (
                        $_SESSION["carrito"] as $itemCarrito
                    ): ?>

                        <?php

                        $precio = (float) $itemCarrito["precio"];
                        $cantidad = (int) $itemCarrito["cantidad"];
                        $subtotal = $precio * $cantidad;

                        ?>

                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $itemCarrito["nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    $precio,
                                    0,
                                    ",",
                                    "."
                                );
                                ?>
                            </td>

                            <td>
                                <?php echo $cantidad; ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    $subtotal,
                                    0,
                                    ",",
                                    "."
                                );
                                ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="3">
                            Total estimado
                        </th>

                        <th>
                            $<?php
                            echo number_format(
                                $totalEstimado,
                                0,
                                ",",
                                "."
                            );
                            ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <p>
            El precio y el stock se verificarán nuevamente
            en la base de datos antes de registrar la compra.
        </p>
    </section>

    <section class="review-section">
        <h2>Datos del cliente</h2>

        <?php if (!$hayClientes): ?>

            <div class="mensaje-error">
                Debe registrar al menos un cliente
                antes de finalizar la compra.
            </div>

            <div class="acciones-inferiores">
                <a
                    href="clientes.php"
                    class="boton-pedido"
                >
                    Registrar cliente
                </a>
            </div>

        <?php else: ?>

            <form
                action="finalizar_compra.php"
                method="post"
                class="review-form"
                id="form-finalizar-compra"
            >
                <input
                    type="hidden"
                    name="token_checkout"
                    value="<?php
                    echo htmlspecialchars(
                        $_SESSION["token_checkout"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>"
                >

                <label for="id_cliente">
                    Cliente
                </label>

                <select
                    id="id_cliente"
                    name="id_cliente"
                    required
                >
                    <option value="">
                        Seleccione un cliente...
                    </option>

                    <?php while (
                        $cliente =
                            $resultadoClientes->fetch_assoc()
                    ): ?>

                        <option
                            value="<?php
                            echo (int) $cliente["id_cliente"];
                            ?>"
                        >
                            <?php
                            echo htmlspecialchars(
                                $cliente["nombre"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $cliente["email"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </option>

                    <?php endwhile; ?>
                </select>

                <button type="submit">
                    Confirmar y registrar compra
                </button>
            </form>

        <?php endif; ?>
    </section>

<?php elseif ($tipoMensaje !== "exito"): ?>

    <section class="review-section">
        <div class="carrito-vacio">
            <h2>No existen productos para comprar</h2>

            <p>
                Debe agregar al menos un producto
                antes de finalizar la compra.
            </p>

            <a
                href="index.php"
                class="boton-pedido"
            >
                Ver productos
            </a>
        </div>
    </section>

<?php endif; ?>

<div class="acciones-inferiores">
    <a
        href="carrito.php"
        class="boton-pedido boton-secundario"
    >
        Volver al carrito
    </a>

    <a
        href="index.php"
        class="boton-pedido"
    >
        Seguir comprando
    </a>
</div>

</body>
</html>

<?php
$conn->close();
?>