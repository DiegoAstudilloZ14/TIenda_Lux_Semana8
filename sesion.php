<?php

// Inicia la sesión solamente si todavía no está activa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tiempo máximo de inactividad: 30 minutos.
$tiempoMaximo = 1800;

// Verifica el tiempo de inactividad.
if (isset($_SESSION["ultima_actividad"])) {
    $inactividad = time() - $_SESSION["ultima_actividad"];

    if ($inactividad > $tiempoMaximo) {
        session_unset();
        session_destroy();

        header("Location: index.php?sesion=expirada");
        exit();
    }
}

// Actualiza el momento de la última actividad.
$_SESSION["ultima_actividad"] = time();

// Usuario predeterminado.
if (!isset($_SESSION["usuario"])) {
    $_SESSION["usuario"] = "Cliente invitado";
}

// Inicializa el carrito.
if (
    !isset($_SESSION["carrito"]) ||
    !is_array($_SESSION["carrito"])
) {
    $_SESSION["carrito"] = [];
}

// Calcula el total de unidades almacenadas en el carrito.
$totalProductos = 0;

foreach ($_SESSION["carrito"] as $productoCarrito) {
    if (
        isset($productoCarrito["cantidad"]) &&
        is_numeric($productoCarrito["cantidad"])
    ) {
        $totalProductos += (int) $productoCarrito["cantidad"];
    }
}