<header class="barra-navegacion">
    <a href="index.php" class="marca-tienda">
        Tienda Electrónica
    </a>

    <nav class="menu-principal">
        <a href="index.php">
            Inicio
        </a>

        <a href="productos.php">
            Gestionar productos
        </a>

        <a href="clientes.php">
            Gestionar clientes
        </a>

        <a href="compras.php">
            Registrar compra
        </a>

        <a href="clientes_frecuentes.php">
            Clientes frecuentes
        </a>
    </nav>

    <div class="datos-sesion">
        <span class="usuario-barra">
            <?php
            echo htmlspecialchars(
                $_SESSION["usuario"],
                ENT_QUOTES,
                "UTF-8"
            );
            ?>
        </span>

        <a href="carrito.php" class="carrito-barra">
            Carrito: <?php echo $totalProductos; ?>
        </a>
    </div>
</header>