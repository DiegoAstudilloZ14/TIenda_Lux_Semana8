<?php
require_once("conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: clientes.php");
    exit();
}

// Recupera y limpia los datos.
$nombre = trim($_POST["nombre"] ?? "");
$email = trim($_POST["email"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");

// Valida que el nombre contenga caracteres permitidos.
$nombreValido = preg_match(
    "/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]+$/u",
    $nombre
);

// Validación en el servidor.
if (
    $nombre === "" ||
    $email === "" ||
    $direccion === "" ||
    mb_strlen($nombre) < 3 ||
    mb_strlen($nombre) > 100 ||
    mb_strlen($email) > 120 ||
    mb_strlen($direccion) > 200 ||
    !$nombreValido ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    die("Los datos ingresados no son válidos.");
}

// Consulta preparada.
$sql = "INSERT INTO cliente
        (nombre, email, direccion)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("No fue posible preparar la consulta.");
}

$stmt->bind_param(
    "sss",
    $nombre,
    $email,
    $direccion
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: clientes.php?registro=exitoso");
    exit();
}

$stmt->close();
$conn->close();

header("Location: clientes.php?error=registro");
exit();
?>