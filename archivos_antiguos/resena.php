<?php

function registrarResena($producto, $calificacion, $comentario){
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<title>Reseña registrada</title>";
    echo "<link rel='stylesheet' href='styles.css'>";
    echo "</head>";
    echo "<body>";
    echo "<div class='review-result'>";
    echo "<h2>Reseña registrada correctamente</h2>";
    echo "<p><strong>Producto:</strong> $producto</p>";
    echo "<p><strong>Calificación:</strong> $calificacion estrellas</p>";
    echo "<p><strong>Comentario:</strong> $comentario</p>";
    echo "<br>";
    echo "<a href='index.php'>Volver a la tienda</a>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $producto = htmlspecialchars($_POST["producto"]);
    $calificacion = htmlspecialchars($_POST["calificacion"]);
    $comentario = htmlspecialchars($_POST["comentario"]);

    registrarResena($producto, $calificacion, $comentario);
}
?>

//pendientes seccion reseñas
<section class="review-section">
    <h2>Calificación y reseña de productos</h2>
    <form action="resena.php" method="post" class="review-form">
        <label for="producto">Producto comprado</label>
        <select id="producto" name="producto" required>
            <option value="">Seleccione un producto</option>
            <?php
            // Ejecuta nuevamente la consulta para llenar el select.
            $resultadoResenas = $conn->query($sqlProductos);
            ?>
            <?php if ($resultadoResenas): ?>
                <?php while (
                    $productoResena =$resultadoResenas->fetch_assoc()
                    ): ?>
                    <option value="<?php echo htmlspecialchars($productoResena["nombre"]);?>">
                        <?php echo htmlspecialchars($productoResena["nombre"]);?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
        <label for="calificacion">Calificación</label>
        <select id="calificacion" name="calificacion" required>
            <option value="">Seleccione</option>
            <option value="5">5 estrellas - Excelente</option>
            <option value="4">4 estrellas - Muy bueno</option>
            <option value="3">3 estrellas - Bueno</option>
            <option value="2">2 estrellas - Regular</option>
            <option value="1">1 estrella - Malo</option>
        </select>
        <label for="comentario">Reseña</label>
        <textarea id="comentario" name="comentario" rows="4" maxlength="250" placeholder="Escriba su opinión del producto"required>
        </textarea>
        <button type="submit">
            Enviar reseña
        </button>
    </form>
</section>