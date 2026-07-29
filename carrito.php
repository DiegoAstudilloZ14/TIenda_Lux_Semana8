<?php

require_once("sesion.php");

$totalCompra = 0;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Carrito de compras</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="encabezado-tienda">
    <h1>Carrito de compras</h1>

    <!-- Mensaje por falta de stock. -->
    <?php if (
        isset($_GET["error"]) &&
        $_GET["error"] === "stock"
    ): ?>
        <div class="mensaje-error">
            No existe stock suficiente para actualizar
            la cantidad solicitada.
        </div>
    <?php endif; ?>

    <!-- Mensaje por cantidad inválida. -->
    <?php if (
        isset($_GET["error"]) &&
        $_GET["error"] === "cantidad"
    ): ?>
        <div class="mensaje-error">
            La cantidad ingresada debe ser un número
            entero mayor que cero.
        </div>
    <?php endif; ?>

    <!-- Mensaje por producto no encontrado. -->
    <?php if (
        isset($_GET["error"]) &&
        $_GET["error"] === "producto"
    ): ?>
        <div class="mensaje-error">
            El producto seleccionado no existe
            en el carrito.
        </div>
    <?php endif; ?>

    <!-- Mensaje de actualización correcta. -->
    <?php if (
        isset($_GET["actualizado"]) &&
        $_GET["actualizado"] === "exitoso"
    ): ?>
        <div class="mensaje-exito">
            Cantidad actualizada correctamente.
        </div>
    <?php endif; ?>

    <!-- Mensaje de eliminación correcta. -->
    <?php if (
        isset($_GET["eliminado"]) &&
        $_GET["eliminado"] === "exitoso"
    ): ?>
        <div class="mensaje-exito">
            Producto eliminado correctamente.
        </div>
    <?php endif; ?>

    <!-- Mensaje de carrito vaciado. -->
    <?php if (
        isset($_GET["vaciado"]) &&
        $_GET["vaciado"] === "exitoso"
    ): ?>
        <div class="mensaje-exito">
            El carrito fue vaciado correctamente.
        </div>
    <?php endif; ?>

    <p>
        Revisa los productos seleccionados
        antes de continuar.
    </p>
</section>

<section class="review-section carrito-contenedor">

    <?php if (empty($_SESSION["carrito"])): ?>

        <div class="carrito-vacio">
            <h2>Tu carrito está vacío</h2>

            <p>
                Todavía no has agregado productos
                a tu compra.
            </p>

            <a
                href="index.php"
                class="boton-pedido"
            >
                Ver productos
            </a>
        </div>

    <?php else: ?>

        <?php foreach (
            $_SESSION["carrito"] as $idProducto => $producto
        ): ?>

            <?php

            $precio = (float) $producto["precio"];
            $cantidad = (int) $producto["cantidad"];
            $subtotal = $precio * $cantidad;

            $totalCompra += $subtotal;

            ?>

            <article class="cart-item">
                <h3>
                    <?php
                    echo htmlspecialchars(
                        $producto["nombre"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </h3>

                <p>
                    <strong>Precio unitario:</strong>

                    $<?php
                    echo number_format(
                        $precio,
                        0,
                        ",",
                        "."
                    );
                    ?>
                </p>

                <p>
                    <strong>Cantidad:</strong>

                    <?php echo $cantidad; ?>
                </p>

                <p>
                    <strong>Subtotal:</strong>

                    $<?php
                    echo number_format(
                        $subtotal,
                        0,
                        ",",
                        "."
                    );
                    ?>
                </p>

                <div class="acciones-carrito">

                    <form
                        action="actualizar_carrito.php"
                        method="post"
                    >
                        <input
                            type="hidden"
                            name="id"
                            value="<?php
                            echo (int) $idProducto;
                            ?>"
                        >

                        <label
                            for="cantidad-<?php
                            echo (int) $idProducto;
                            ?>"
                        >
                            Actualizar cantidad
                        </label>

                        <input
                            type="number"
                            id="cantidad-<?php
                            echo (int) $idProducto;
                            ?>"
                            name="cantidad"
                            min="1"
                            step="1"
                            value="<?php echo $cantidad; ?>"
                            required
                        >

                        <button type="submit">
                            Actualizar
                        </button>
                    </form>

                    <form
                        action="eliminar_carrito.php"
                        method="post"
                    >
                        <input
                            type="hidden"
                            name="id"
                            value="<?php
                            echo (int) $idProducto;
                            ?>"
                        >

                        <button
                            type="submit"
                            class="boton-eliminar"
                        >
                            Eliminar producto
                        </button>
                    </form>

                </div>
            </article>

        <?php endforeach; ?>

        <div class="resumen-carrito">
            <h2>
                Total de la compra:

                $<?php
                echo number_format(
                    $totalCompra,
                    0,
                    ",",
                    "."
                );
                ?>
            </h2>

            <div class="acciones-finales">

                <form
                    action="vaciar_carrito.php"
                    method="post"
                >
                    <button
                        type="submit"
                        class="boton-pedido boton-secundario"
                    >
                        Vaciar carrito
                    </button>
                </form>

                <a
                    href="finalizar_compra.php"
                    class="boton-pedido"
                >
                    Finalizar Compra
                </a>

            </div>
        </div>

    <?php endif; ?>

</section>

<div class="acciones-inferiores">
    <a
        href="index.php"
        class="boton-pedido"
    >
        Volver a la tienda
    </a>

    <a
        href="cerrar_sesion.php"
        class="boton-pedido boton-cerrar"
    >
        Cerrar sesión
    </a>
</div>

</body>
</html>