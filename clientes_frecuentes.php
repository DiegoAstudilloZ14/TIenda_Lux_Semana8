<?php

require_once("sesion.php");
require_once("conexion.php");

/*
Consulta 1:
Calcula el número de compras realizadas por cada cliente.

Se utiliza LEFT JOIN para mostrar también a los clientes
que todavía no registran compras.
*/
$sqlResumen = "SELECT
                    cliente.nombre,
                    cliente.email,
                    COUNT(compra.id_compra) AS total_compras
               FROM cliente
               LEFT JOIN compra
                    ON cliente.id_cliente = compra.id_cliente
               GROUP BY
                    cliente.id_cliente,
                    cliente.nombre,
                    cliente.email
               ORDER BY total_compras DESC,
                        cliente.nombre ASC";

$resultadoResumen = $conn->query($sqlResumen);

/*
Consulta 2:
Muestra solamente a los clientes que han realizado
más de dos compras.
*/
$sqlFrecuentes = "SELECT
                       cliente.nombre,
                       cliente.email,
                       COUNT(compra.id_compra) AS total_compras
                  FROM cliente
                  INNER JOIN compra
                       ON cliente.id_cliente = compra.id_cliente
                  GROUP BY
                       cliente.id_cliente,
                       cliente.nombre,
                       cliente.email
                  HAVING COUNT(compra.id_compra) > 2
                  ORDER BY total_compras DESC,
                           cliente.nombre ASC";

$resultadoFrecuentes = $conn->query($sqlFrecuentes);

// Verifica que ambas consultas se ejecutaran correctamente.
if (!$resultadoResumen || !$resultadoFrecuentes) {
    $conn->close();

    die(
        "No fue posible recuperar el resumen " .
        "de compras por cliente."
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

    <title>Resumen de compras por cliente</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="encabezado-tienda">
    <h1>Consultas de compras por cliente</h1>

    <p>
        Resumen de las operaciones registradas en la tienda.
    </p>
</section>

<section class="review-section">
    <!-- Resumen de compras por cliente. -->

    <h2>Número de compras realizadas por cliente</h2>

    <p>
        Esta tabla muestra a todos los clientes registrados
        y la cantidad de compras asociadas a cada uno.
    </p>

    <?php if ($resultadoResumen->num_rows > 0): ?>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Correo electrónico</th>
                        <th>Número de compras</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while (
                        $filaResumen = $resultadoResumen->fetch_assoc()
                    ): ?>

                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $filaResumen["nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $filaResumen["email"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo (int) $filaResumen["total_compras"];
                                ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p>
            No existen clientes registrados
            en la base de datos.
        </p>

    <?php endif; ?>
</section>

<section class="review-section">
    <!-- Clientes con más de dos compras. -->

    <h2>Clientes con más de dos compras</h2>

    <p>
        Esta tabla muestra únicamente a los clientes
        que superan las dos compras registradas.
    </p>

    <?php if ($resultadoFrecuentes->num_rows > 0): ?>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Correo electrónico</th>
                        <th>Número de compras</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while (
                        $filaFrecuente =
                            $resultadoFrecuentes->fetch_assoc()
                    ): ?>

                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $filaFrecuente["nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $filaFrecuente["email"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo (int)
                                    $filaFrecuente["total_compras"];
                                ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p>
            No existen clientes con más de dos
            compras registradas.
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

</body>
</html>

<?php
$conn->close();
?>