<?php

session_start();

// Elimina todas las variables de sesión.
session_unset();

// Elimina la cookie de sesión si está habilitada.
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}

// Destruye completamente la sesión.
session_destroy();

// Redirige a la tienda.
header("Location: index.php?sesion=cerrada");
exit();