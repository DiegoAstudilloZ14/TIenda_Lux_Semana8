<?php

require_once("sesion.php");
require_once("conexion.php");

// Recupera los productos desde MySQL.
$sqlProductos = "SELECT
                    id_producto,
                    nombre,
                    descripcion,
                    precio,
                    stock
                 FROM producto
                 ORDER BY nombre ASC";

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

    <link rel="stylesheet" href="styles.css">

    <title>Tienda de Comercio Electrónico</title>
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="encabezado-tienda">
    <h1>Tienda de Comercio Electrónico</h1>

    <p>
        Busca productos, consulta su disponibilidad
        y agrégalos al carrito.
    </p>
</section>

<?php if (
    isset($_GET["sesion"]) &&
    $_GET["sesion"] === "expirada"
): ?>
    <div class="mensaje-error">
        La sesión terminó por inactividad.
    </div>
<?php endif; ?>

<?php if (
    isset($_GET["sesion"]) &&
    $_GET["sesion"] === "cerrada"
): ?>
    <div class="mensaje-exito">
        La sesión fue cerrada correctamente.
    </div>
<?php endif; ?>

<div class="search-container">

    <input
        type="text"
        id="product-search"
        placeholder="Buscar producto"
    >

    <select id="availability-filter">
        <option value="todos">
            Todos
        </option>

        <option value="disponible">
            Disponibles
        </option>

        <option value="sin-stock">
            Sin stock
        </option>
    </select>

    <button
        type="button"
        id="search-button"
    >
        Buscar
    </button>
</div>

<!-- Contenedor de notificaciones. -->
<div id="notification-container"></div>

<!-- Contenedor de resultados de búsqueda. -->
<div id="results-container">

    <?php if ($resultadoProductos->num_rows > 0): ?>

        <?php while (
            $producto = $resultadoProductos->fetch_assoc()
        ): ?>

            <?php
            $disponibilidad = (int) $producto["stock"] > 0
                ? "disponible"
                : "sin-stock";
            ?>

            <article
                class="product-card"
                data-nombre="<?php
                echo htmlspecialchars(
                    strtolower($producto["nombre"]),
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>"
                data-disponibilidad="<?php
                echo $disponibilidad;
                ?>"
            >
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
                    <?php
                    echo htmlspecialchars(
                        $producto["descripcion"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </p>

                <p>
                    <strong>Precio:</strong>

                    $<?php
                    echo number_format(
                        $producto["precio"],
                        0,
                        ",",
                        "."
                    );
                    ?>
                </p>

                <p>
                    <strong>Stock:</strong>

                    <?php echo (int) $producto["stock"]; ?>
                </p>

                <?php if ((int) $producto["stock"] > 0): ?>

                    <p class="estado-disponible">
                        Disponible
                    </p>

                    <form
                        action="agregar_carrito.php"
                        method="post"
                    >
                        <input
                            type="hidden"
                            name="id"
                            value="<?php
                            echo (int) $producto["id_producto"];
                            ?>"
                        >

                        <button
                            type="submit"
                            class="add-cart-button"
                        >
                            Agregar al carrito
                        </button>
                    </form>

                <?php else: ?>

                    <p class="estado-sin-stock">
                        Sin stock
                    </p>

                    <button
                        type="button"
                        class="add-cart-button"
                        disabled
                    >
                        Producto no disponible
                    </button>

                <?php endif; ?>

            </article>

        <?php endwhile; ?>

    <?php else: ?>

        <p id="mensaje-sin-productos">
            No existen productos registrados
            en la base de datos.
        </p>

    <?php endif; ?>

</div>

<script src="script.js"></script>

</body>
</html>

<?php
$conn->close();
?>