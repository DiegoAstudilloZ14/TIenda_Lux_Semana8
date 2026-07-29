<?php

require_once("conexion.php");

// Solo permite solicitudes mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: productos.php");
    exit();
}

// Recupera y limpia los datos recibidos.
$nombre = trim($_POST["nombre"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");

$precio = filter_input(
    INPUT_POST,
    "precio",
    FILTER_VALIDATE_FLOAT
);

$stock = filter_input(
    INPUT_POST,
    "stock",
    FILTER_VALIDATE_INT
);

// Valida los datos ingresados.
if (
    $nombre === "" ||
    $descripcion === "" ||
    $precio === false ||
    $precio === null ||
    $precio <= 0 ||
    $stock === false ||
    $stock === null ||
    $stock < 0
) {
    $conn->close();

    header("Location: productos.php?error=datos");
    exit();
}

// Valida la longitud de los textos.
if (
    mb_strlen($nombre, "UTF-8") > 100 ||
    mb_strlen($descripcion, "UTF-8") > 250
) {
    $conn->close();

    header("Location: productos.php?error=longitud");
    exit();
}

try {
    // Inicia una transacción.
    if (!$conn->begin_transaction()) {
        throw new RuntimeException(
            "No fue posible iniciar la transacción."
        );
    }

    /*
    Busca un producto existente con el mismo nombre.

    LOWER y TRIM permiten detectar nombres escritos con
    diferencias de mayúsculas o espacios.
    */
    $sqlBuscarProducto = "SELECT
                              id_producto,
                              stock
                          FROM producto
                          WHERE LOWER(TRIM(nombre)) =
                                LOWER(TRIM(?))
                          LIMIT 1
                          FOR UPDATE";

    $stmtBuscarProducto = $conn->prepare(
        $sqlBuscarProducto
    );

    if (!$stmtBuscarProducto) {
        throw new RuntimeException(
            "No fue posible preparar la búsqueda del producto."
        );
    }

    $stmtBuscarProducto->bind_param(
        "s",
        $nombre
    );

    if (!$stmtBuscarProducto->execute()) {
        throw new RuntimeException(
            "No fue posible buscar el producto."
        );
    }

    $resultadoProducto =
        $stmtBuscarProducto->get_result();

    /*
    Si el producto ya existe, se actualizan sus datos
    y se suma el nuevo stock al existente.
    */
    if ($resultadoProducto->num_rows === 1) {
        $productoExistente =
            $resultadoProducto->fetch_assoc();

        $idProducto = (int)
            $productoExistente["id_producto"];

        $sqlActualizarProducto = "UPDATE producto
                                  SET descripcion = ?,
                                      precio = ?,
                                      stock = stock + ?
                                  WHERE id_producto = ?";

        $stmtActualizarProducto = $conn->prepare(
            $sqlActualizarProducto
        );

        if (!$stmtActualizarProducto) {
            throw new RuntimeException(
                "No fue posible preparar la actualización."
            );
        }

        $stmtActualizarProducto->bind_param(
            "sdii",
            $descripcion,
            $precio,
            $stock,
            $idProducto
        );

        if (!$stmtActualizarProducto->execute()) {
            throw new RuntimeException(
                "No fue posible actualizar el producto."
            );
        }

        if ($stmtActualizarProducto->affected_rows !== 1) {
            throw new RuntimeException(
                "El producto no pudo ser actualizado."
            );
        }

        $stmtActualizarProducto->close();
        $stmtBuscarProducto->close();

        if (!$conn->commit()) {
            throw new RuntimeException(
                "No fue posible confirmar la actualización."
            );
        }

        $conn->close();

        header(
            "Location: productos.php?registro=actualizado"
        );
        exit();
    }

    /*
    Si el producto no existe, se registra normalmente.
    */
    $sqlInsertarProducto = "INSERT INTO producto (
                                nombre,
                                descripcion,
                                precio,
                                stock
                            )
                            VALUES (?, ?, ?, ?)";

    $stmtInsertarProducto = $conn->prepare(
        $sqlInsertarProducto
    );

    if (!$stmtInsertarProducto) {
        throw new RuntimeException(
            "No fue posible preparar el registro."
        );
    }

    $stmtInsertarProducto->bind_param(
        "ssdi",
        $nombre,
        $descripcion,
        $precio,
        $stock
    );

    if (!$stmtInsertarProducto->execute()) {
        throw new RuntimeException(
            "No fue posible registrar el producto."
        );
    }

    $stmtInsertarProducto->close();
    $stmtBuscarProducto->close();

    if (!$conn->commit()) {
        throw new RuntimeException(
            "No fue posible confirmar el registro."
        );
    }

    $conn->close();

    header("Location: productos.php?registro=exitoso");
    exit();

} catch (Throwable $error) {
    $conn->rollback();
    $conn->close();

    header("Location: productos.php?error=registro");
    exit();
}