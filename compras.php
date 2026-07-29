<?php

require_once("sesion.php");
require_once("conexion.php");

// Recupera los clientes registrados.
$sqlClientes = "SELECT
                    id_cliente,
                    nombre
                FROM cliente
                ORDER BY nombre ASC";

$clientes = $conn->query($sqlClientes);

// Recupera solamente productos con stock disponible.
$sqlProductos = "SELECT
                    id_producto,
                    nombre,
                    precio,
                    stock
                 FROM producto
                 WHERE stock > 0
                 ORDER BY nombre ASC";

$productos = $conn->query($sqlProductos);

// Verifica que ambas consultas se ejecutaran correctamente.
if (!$clientes || !$productos) {
    $conn->close();

    die(
        "No fue posible recuperar la información " .
        "necesaria para registrar la compra."
    );
}

// Comprueba si existen clientes y productos disponibles.
$hayClientes = $clientes->num_rows > 0;
$hayProductos = $productos->num_rows > 0;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registrar compra</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="review-section">
    <h2>Registrar compra</h2>

    <?php if (
        isset($_GET["registro"]) &&
        $_GET["registro"] === "exitoso"
    ): ?>
        <div class="mensaje-exito">
            Compra registrada correctamente.
        </div>
    <?php endif; ?>

    <?php if (
        isset($_GET["error"]) &&
        $_GET["error"] === "registro"
    ): ?>
        <div class="mensaje-error">
            No fue posible registrar la compra.
        </div>
    <?php endif; ?>

    <?php if (!$hayClientes): ?>
        <div class="mensaje-error">
            Debe registrar al menos un cliente
            antes de realizar una compra.
        </div>
    <?php endif; ?>

    <?php if (!$hayProductos): ?>
        <div class="mensaje-error">
            No existen productos con stock disponible.
        </div>
    <?php endif; ?>

    <form
        action="registrar_compra.php"
        method="post"
        class="review-form"
        id="form-compra"
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
                Seleccione...
            </option>

            <?php if ($hayClientes): ?>

                <?php while (
                    $cliente = $clientes->fetch_assoc()
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
                    </option>

                <?php endwhile; ?>

            <?php endif; ?>
        </select>

        <label for="id_producto">
            Producto
        </label>

        <select
            id="id_producto"
            name="id_producto"
            required
        >
            <option value="">
                Seleccione...
            </option>

            <?php if ($hayProductos): ?>

                <?php while (
                    $producto = $productos->fetch_assoc()
                ): ?>

                    <option
                        value="<?php
                        echo (int) $producto["id_producto"];
                        ?>"
                    >
                        <?php
                        echo htmlspecialchars(
                            $producto["nombre"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                        ($<?php
                        echo number_format(
                            $producto["precio"],
                            0,
                            ",",
                            "."
                        );
                        ?>)

                        - Stock:
                        <?php echo (int) $producto["stock"]; ?>
                    </option>

                <?php endwhile; ?>

            <?php endif; ?>
        </select>

        <label for="cantidad">
            Cantidad
        </label>

        <input
            type="number"
            id="cantidad"
            name="cantidad"
            min="1"
            step="1"
            required
        >

        <button
            type="submit"
            <?php
            echo (!$hayClientes || !$hayProductos)
                ? "disabled"
                : "";
            ?>
        >
            Registrar compra
        </button>
    </form>
</section>

<div class="acciones-inferiores">
    <a
        href="index.php"
        class="boton-pedido"
    >
        Volver a la tienda
    </a>
</div>

</body>
</html>

<?php
$conn->close();
?>