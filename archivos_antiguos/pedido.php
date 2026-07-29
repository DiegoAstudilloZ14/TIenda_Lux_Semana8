<?php
//Clase que representa un pedido realizado en la tienda.
class Pedido {
    //Propiedades del pedido.
    public $descripcion;
    public $tipo;
    public $producto;
    public $unidades;
    public $observaciones;

    //Constructor para inicializar los datos del pedido.

    public function __construct($descripcion, $tipo, $producto, $unidades, $observaciones){
        $this->descripcion = $descripcion;
        $this->tipo = $tipo;
        $this->producto = $producto;
        $this->unidades = $unidades;
        $this->observaciones = $observaciones; 
    }
    //Método para registrar pedido
    public function registrarPedido(){
        return "Pedido registrado correctamente";
    }
    //Método para mostrar la información completa del pedido.
    public function mostrarPedido(){
        return "
            <h2>Información del pedido</h2>
            <p><strong>Descripción:</strong> {$this->descripcion}</p>
            <p><strong>Tipo de pedido:</strong> {$this->tipo}</p>
            <p><strong>Producto:</strong> {$this->producto}</p>
            <p><strong>Unidades:</strong> {$this->unidades}</p>
            <p><strong>Observaciones:</strong> {$this->observaciones}</p>
            ";
    }
    //Método para buscar si el pedido corresponde a un producto específico.
    public function buscarPorProducto($productoBuscado){
        if (strtolower($this->producto) == strtolower($productoBuscado)){
            return "El pedido coincide con el producto buscado.";
        } else{
            return "El pedido no coincide con el producto buscado.";
        }
    }
    //Método para buscar si el pedido corresponde a un tipo específico.
    public function buscarPorTipo($tipoBuscado){
        if(strtolower($this->tipo) == strtolower($tipoBuscado)){
            return "El pedido coincide con el tipo de pedido buscado.";
        } else{
            return "El pedido no coincide con el tipo de pedido buscado.";
        }
    }
}