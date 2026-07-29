// Selección de los elementos de búsqueda y filtrado.
const inputBusqueda = document.getElementById("product-search");
const filtroDisponibilidad = document.getElementById("availability-filter");
const botonBuscar = document.getElementById("search-button");
const contenedorResultados = document.getElementById("results-container");
const contenedorNotificacion = document.getElementById("notification-container");

// Recupera las tarjetas generadas por PHP.
const tarjetasProductos = document.querySelectorAll(".product-card");

// Filtra los productos según nombre y disponibilidad.
function buscarProductos() {
    const textoBusqueda = inputBusqueda.value.trim().toLowerCase();
    const disponibilidadSeleccionada = filtroDisponibilidad.value;
    let productosEncontrados = 0;
    tarjetasProductos.forEach(function(tarjeta) {
        const nombreProducto = tarjeta.dataset.nombre;
        const disponibilidadProducto = tarjeta.dataset.disponibilidad;
        const coincideNombre = nombreProducto.includes(textoBusqueda);
        const coincideDisponibilidad = disponibilidadSeleccionada === "todos" ||
            disponibilidadProducto === disponibilidadSeleccionada;
        if (coincideNombre && coincideDisponibilidad) {
            tarjeta.style.display = "block";
            productosEncontrados++;
        } else {
            tarjeta.style.display = "none";
        }
    });
    mostrarMensajeBusqueda(productosEncontrados);
}

// Muestra un mensaje cuando no hay coincidencias.
function mostrarMensajeBusqueda(productosEncontrados) {
    let mensajeAnterior =
        document.getElementById("mensaje-busqueda");
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }

    if (productosEncontrados === 0 && tarjetasProductos.length > 0) {

        const mensaje = document.createElement("p");
        mensaje.id = "mensaje-busqueda";
        mensaje.textContent = "No se encontraron productos con los criterios seleccionados.";
        contenedorResultados.appendChild(mensaje);
    }
}

// Muestra una notificación temporal.
function mostrarNotificacion(mensaje) {
    contenedorNotificacion.textContent = mensaje;
    setTimeout(function() {
        contenedorNotificacion.textContent = "";
    }, 4000);
}
// Eventos.
botonBuscar.addEventListener("click", buscarProductos);
inputBusqueda.addEventListener("input", buscarProductos);
filtroDisponibilidad.addEventListener("change", function() {
    buscarProductos();
    mostrarNotificacion("Filtro de disponibilidad aplicado correctamente.");
    }
);