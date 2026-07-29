<?php

require_once("sesion.php");
require_once("conexion.php");

// Recupera los clientes registrados.
$sqlClientes = "SELECT
                    id_cliente,
                    nombre,
                    email,
                    direccion
                FROM cliente
                ORDER BY id_cliente DESC";

$resultadoClientes = $conn->query($sqlClientes);

// Verifica que la consulta se haya ejecutado correctamente.
if (!$resultadoClientes) {
    $conn->close();

    die(
        "No fue posible recuperar los clientes " .
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

    <title>Gestión de clientes</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php require("barra_navegacion.php"); ?>

<section class="review-section">
    <h2>Registrar cliente</h2>

    <?php if (
        isset($_GET["registro"]) &&
        $_GET["registro"] === "exitoso"
    ): ?>
        <div class="mensaje-exito">
            Cliente registrado correctamente.
        </div>
    <?php endif; ?>

    <?php if (
        isset($_GET["error"]) &&
        $_GET["error"] === "registro"
    ): ?>
        <div class="mensaje-error">
            No fue posible registrar el cliente.
        </div>
    <?php endif; ?>

    <form
        action="registrar_cliente.php"
        method="post"
        class="review-form"
        id="form-cliente"
    >
        <label for="nombre">
            Nombre completo
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            autocomplete="name"
            required
        >

        <label for="email">
            Correo electrónico
        </label>

        <input
            type="email"
            id="email"
            name="email"
            maxlength="120"
            autocomplete="email"
            required
        >

        <label for="direccion">
            Dirección
        </label>

        <input
            type="text"
            id="direccion"
            name="direccion"
            maxlength="200"
            autocomplete="street-address"
            required
        >

        <button type="submit">
            Registrar cliente
        </button>
    </form>
</section>

<section class="review-section">
    <h2>Clientes registrados</h2>

    <?php if ($resultadoClientes->num_rows > 0): ?>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Dirección</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while (
                        $cliente = $resultadoClientes->fetch_assoc()
                    ): ?>

                        <tr>
                            <td>
                                <?php
                                echo (int) $cliente["id_cliente"];
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente["nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente["email"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente["direccion"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p>
            No existen clientes registrados.
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
const formularioCliente = document.getElementById("form-cliente");

if (formularioCliente) {
    formularioCliente.addEventListener("submit", function (event) {
        const nombre = document
            .getElementById("nombre")
            .value
            .trim();

        const email = document
            .getElementById("email")
            .value
            .trim();

        const direccion = document
            .getElementById("direccion")
            .value
            .trim();

        const formatoNombre =
            /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]+$/;

        const formatoCorreo =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (
            nombre === "" ||
            email === "" ||
            direccion === ""
        ) {
            alert("Debe completar todos los campos.");
            event.preventDefault();
            return;
        }

        if (!formatoNombre.test(nombre)) {
            alert(
                "El nombre solo debe contener letras, espacios, " +
                "apóstrofes o guiones."
            );

            event.preventDefault();
            return;
        }

        if (!formatoCorreo.test(email)) {
            alert(
                "Debe ingresar un correo electrónico válido."
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