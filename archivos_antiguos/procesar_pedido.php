<?php
require_once("pedido.php");
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $descripcion = htmlspecialchars($_POST["descripcion"]);
    $tipo = htmlspecialchars($_POST["tipo"]);
    $producto = htmlspecialchars($_POST["producto"]);
    $unidades = htmlspecialchars($_POST["unidades"]);
    $observaciones = htmlspecialchars($_POST["observaciones"]);

    $pedido = new Pedido(
        $descripcion,
        $tipo,
        $producto,
        $unidades,
        $observaciones
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido Registrado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="review-result">
    <?php
    echo "<h3>" . $pedido->registrarPedido() . "</h3>";
    echo $pedido->mostrarPedido();
    echo "<hr>";
    echo "<p><strong>Datos recibidos correctamente mediante el método POST.</p>";
    ?>
    <a href="pedido.html">Registrar otro pedido</a>
    <br><br>
    <a href="tienda.html">Volver a la tienda</a>
</div>
</body>
</html>
