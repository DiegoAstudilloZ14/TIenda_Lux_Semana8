<?php
session_start();
//Implementamos tiempo maximo de sesión 
$tiempoMaximo = 1800; // 30 minutos

if (isset($_SESSION["ultima_actividad"])) {

    $inactividad = time() - $_SESSION["ultima_actividad"];

    if ($inactividad > $tiempoMaximo) {

        session_unset();
        session_destroy();

        header("Location: index.php");
        exit();
    }
}
//Registramos la actividad del usuario en la sesión
$_SESSION["ultima_actividad"] = time();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Pedido</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<section class="review-section">
    <h2>Registro de Pedidos</h2>

    <form action="procesar_pedido.php" method="post" class="review-form">
        <label for="descripcion">Descripción del pedido</label>
        <input type="text" id="descripcion" name="descripcion" required>

        <label for="tipo">Tipo de pedido</label>
        <select id="tipo" name="tipo" required>
            <option value="">Seleccione una opción</option>
            <option value="Compra Online">Compra Online</option>
            <option value="Retiro en tienda">Retiro en tienda</option>
        </select>

        <label for="producto">Producto</label>
        <select id="producto" name="producto" required>
            <option value="">Seleccione un producto</option>
            <option>Notebook Lenovo</option>
            <option>Mouse inalámbrico</option>
            <option>Teclado mecánico</option>
            <option>Smart TV 50 pulgadas</option>
            <option>Silla escritorio</option>
        </select>

        <label for="unidades">Unidades</label>
        <input type="number" id="unidades" name="unidades" min="1" required>

        <label for="observaciones">Observaciones</label>
        <textarea
            id="observaciones"
            name="observaciones"
            rows="4"
            maxlength="250"
            placeholder="Ingrese observaciones adicionales"></textarea>

        <button type="submit">Registrar Pedido</button>
    </form>

    <div class="pedido-link">
        <a href="index.php" class="boton-pedido">Volver a la tienda</a>
    </div>
</section>

</body>
</html>