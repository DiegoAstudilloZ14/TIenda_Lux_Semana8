<?php

require_once("sesion.php");
require_once("conexion.php");

// Recupera los productos almacenados en MySQL.
$sqlProductos = "SELECT
                    id_producto,
                    nombre,
                    descripcion,
                    precio,
                    stock
                 FROM producto
                 ORDER BY id_producto DESC";

$resultadoProductos = $conn->query($sqlProductos);

// Verifica que la consulta se haya ejecutado correctamente.
if (!$resultadoProductos) {
    $conn->close();

    die(
        "No fue posible recuperar los productos " .
        "desde la base de datos."
    );
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

    <title>Gestión de productos</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="review-section">
    <h2>Registrar producto</h2>

    <?php if (
        isset($_GET["registro"]) &&
        $_GET["registro"] === "exitoso"
    ): ?>
        <div class="mensaje-exito">
            Producto registrado correctamente.
        </div>
    <?php endif; ?>

    <?php if (
        isset($_GET["error"]) &&
        $_GET["error"] === "registro"
    ): ?>
        <div class="mensaje-error">
            No fue posible registrar el producto.
        </div>
    <?php endif; ?>

    <?php if (
    isset($_GET["registro"]) &&
    $_GET["registro"] === "actualizado"
    ): ?>
    <div class="mensaje-exito">
        El producto ya existía. Su información y stock
        fueron actualizados correctamente.
    </div>
    <?php endif; ?>

    <?php if (
    isset($_GET["error"]) &&
    $_GET["error"] === "datos"
    ): ?>
    <div class="mensaje-error">
        Los datos ingresados no son válidos.
    </div>
    <?php endif; ?>

    <?php if (
    isset($_GET["error"]) &&
    $_GET["error"] === "longitud"
    ): ?>
    <div class="mensaje-error">
        El nombre o la descripción superan
        la longitud permitida.
    </div>
    <?php endif; ?>
    
    <form
        action="registrar_producto.php"
        method="post"
        class="review-form"
        id="form-producto"
    >
        <label for="nombre">
            Nombre del producto
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >

        <label for="descripcion">
            Descripción
        </label>

        <textarea
            id="descripcion"
            name="descripcion"
            rows="4"
            maxlength="250"
            required
        ></textarea>

        <label for="precio">
            Precio
        </label>

        <input
            type="number"
            id="precio"
            name="precio"
            min="1"
            step="1"
            required
        >

        <label for="stock">
            Stock disponible
        </label>

        <input
            type="number"
            id="stock"
            name="stock"
            min="0"
            step="1"
            required
        >

        <button type="submit">
            Registrar producto
        </button>
    </form>
</section>

<section class="review-section">
    <h2>Productos registrados</h2>

    <?php if ($resultadoProductos->num_rows > 0): ?>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while (
                        $producto = $resultadoProductos->fetch_assoc()
                    ): ?>

                        <tr>
                            <td>
                                <?php
                                echo (int) $producto["id_producto"];
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $producto["nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $producto["descripcion"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    $producto["precio"],
                                    0,
                                    ",",
                                    "."
                                );
                                ?>
                            </td>

                            <td>
                                <?php echo (int) $producto["stock"]; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p>
            No existen productos registrados.
        </p>

    <?php endif; ?>

    <div class="acciones-inferiores">
        <a
            href="index.php"
            class="boton-pedido"
        >
            Volver a la tienda
        </a>
    </div>
</section>

<script>
const formularioProducto = document.getElementById("form-producto");

if (formularioProducto) {
    formularioProducto.addEventListener("submit", function (event) {
        const nombre = document
            .getElementById("nombre")
            .value
            .trim();

        const descripcion = document
            .getElementById("descripcion")
            .value
            .trim();

        const precio = Number(
            document.getElementById("precio").value
        );

        const stock = Number(
            document.getElementById("stock").value
        );

        if (nombre === "" || descripcion === "") {
            alert(
                "Debe completar el nombre y la descripción " +
                "del producto."
            );

            event.preventDefault();
            return;
        }

        if (!Number.isInteger(precio) || precio <= 0) {
            alert(
                "El precio debe ser un número entero " +
                "mayor que cero."
            );

            event.preventDefault();
            return;
        }

        if (!Number.isInteger(stock) || stock < 0) {
            alert(
                "El stock debe ser un número entero " +
                "igual o mayor que cero."
            );

            event.preventDefault();
        }
    });
}
</script>

</body>
</html>

<?php
$conn->close();
?>