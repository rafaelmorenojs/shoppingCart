<?php

/**
 * Created by PhpStorm.
 * User: Rafael
 * Date: 5/9/2017
 * Time: 09:24
 */
class ShoppingCart {

    //aquí guardamos el contenido del carrito
    private $shoppingCart = array();

    //seteamos el carrito exista o no exista en el constructor
    public function __construct() {
        if (!isset($_SESSION['shoppingCart'])) {
            $_SESSION['shoppingCart'] = NULL;
            $this->shoppingCart['totalPrice'] = 0;
            $this->shoppingCart['totalItems'] = 0;
        }

        $this->shoppingCart = $_SESSION['shoppingCart'];
    }


    /**
     * @param array $item
     * @throws Exception
     */
    public function add($item = array()) {

        /*primero comprobamos el articulo a añadir, si está vacío o no es un
        array lanzamos una excepción y cortamos la ejecución*/
        if (!is_array($item) || empty($item)) {
            throw new Exception("Error, el articulo no es un array", 1);
        }

        //nuestro carro necesita siempre un id producto, cantidad y precio articulo
        if (!$item['id'] || !$item['quantity'] || !$item['price']) {
            throw new Exception("Error, el articulo debe tener un id, cantidad y precio", 1);
        }


        ////nuestro carro necesita siempre un id producto, cantidad y precio articulo
        if (!is_numeric($item['id']) || !is_numeric($item['quantity']) || !is_numeric($item['price'])) {
            throw new Exception("Error, el id, cantidad y precio deben ser numeros!", 1);
        }

        //debemos crear un identificador único para cada producto
        $unique_id = md5($item['id']);

        //creamos la id única para el producto
        if (!empty($this->shoppingCart)) {
            foreach ($this->shoppingCart as $row) {

                /*comprobamos si este producto ya estaba en el
                carrito para actualizar el producto o insertar
                un nuevo producto*/
                if ($row['unique_id'] === $unique_id) {

                    //si ya estaba sumamos la cantidad
                    $item['quantity'] = $row['quantity'] + $item['quantity'];
                }
            }
        }

        //evitamos que nos pongan números negativos y que sólo sean números para cantidad y precio
        $item['quantity'] = trim(preg_replace('/([^0-9\.])/i', '', $item['quantity']));
        $item['price'] = trim(preg_replace('/([^0-9\.])/i', '', $item['price']));

        /*añadimos un elemento total al array carrito para
        saber el precio total de la suma de este artículo*/
        $item['total'] = $item['quantity'] * $item['price'];

        //primero debemos eliminar el producto si es que estaba en el carrito
        $this->unsetItem($unique_id);

        //ahora añadimos el producto al carrito
        $_SESSION['shoppingCart'][$unique_id] = $item;

        //actualizamos el carrito
        $this->updateCart();

        /*actualizamos el precio total y el número de artículos del carrito
        una vez hemos añadido el producto*/
        $this->updatePriceQuantity();
    }


    /**
     * @desc método que actualiza el precio total y la cantidad de productos total del carrito
     */
    private function updatePriceQuantity() {

        //seteamos las variables precio y artículos a 0
        $price = 0;
        $items = 0;

        /*recorrecmos el contenido del carrito para actualizar
        el precio total y el número de artículos*/
        foreach ($this->shoppingCart as $row) {
            $price = $price + ($row['price'] * $row['quantity']);
            $items = $items + $row['quantity'];
        }

        /*asignamos a totalItems el número de artículos actual
        y al precio el precio actual*/
        $_SESSION['shoppingCart']['totalItems'] = $items;
        $_SESSION['shoppingCart']['totalPrice'] = $price;

        //refrescamos él contenido del carrito para que quedé actualizado
        $this->updateCart();
    }


    /**
     * @return int|mixed
     * @throws Exception
     */
    public function totalPrice() {

        /*si no está definido el elemento totalPrice o no existe el carrito
        el precio total será 0*/
        if (!isset($this->shoppingCart['totalPrice']) || $this->carrito === NULL) {
            return 0;
        }

        //si no es númerico lanzamos una excepción porque no es correcto
        if (!is_numeric($this->shoppingCart['totalPrice'])) {
            throw new Exception("El precio total del carrito debe ser un numero", 1);
        }

        //en otro caso devolvemos el precio total del carrito
        return $this->shoppingCart['totalPrice'] ? $this->shoppingCart['totalPrice'] : 0;
    }


    /**
     * @return int|mixed método que retorna el número de artículos del carrito
     * @throws Exception
     */
    public function totalItems() {

        /*si no está definido el elemento totalItems o no existe el carrito
        el número de artículos será de 0*/
        if (!isset($this->shoppingCart['totalItems']) || $this->carrito === NULL) {
            return 0;
        }

        //si no es númerico lanzamos una excepción porque no es correcto
        if (!is_numeric($this->shoppingCart['totalItems'])) {
            throw new Exception("El numero de articulos del carrito debe ser un numero", 1);
        }

        //en otro caso devolvemos el número de artículos del carrito
        return $this->shoppingCart['totalItems'] ? $this->shoppingCart['totalItems'] : 0;
    }


    /**
     * @return array|null método retorna el contenido del carrito
     */
    public function getContent() {

        //asignamos el carrito a una variable
        $carrito = $this->shoppingCart;

        /*debemos eliminar del carrito el número de artículos
        y el precio total para poder mostrar bien los artículos
        ya que estos datos los devuelven los métodos
        totalItems y totalPrice*/
        unset($carrito['totalItems']);
        unset($carrito['totalPrice']);
        return $carrito == NULL ? NULL : $carrito;
    }

    /**
     * @param $unique_id string
     * @desc método que llamamos al insertar un nuevo producto al carrito para eliminarlo si existia, así podemos insertarlo de nuevo pero actualizado
     */
    private function unsetItem($unique_id) {
        unset($_SESSION['shoppingCart'][$unique_id]);
    }

    /**
     * @param $unique_id
     * @return bool
     * @throws Exception
     */
    public function removeItem($unique_id) {

        //si no existe el carrito
        if ($this->shoppingCart === NULL) {
            throw new Exception("El carrito no existe!", 1);
        }

        //si no existe la id única del producto en el carrito
        if (!isset($this->shoppingCart[$unique_id])) {
            throw new Exception("La unique_id " . $unique_id . " no existe!", 1);
        }

        /*en otro caso, eliminamos el producto, actualizamos el carrito y
        el precio y cantidad totales del carrito*/
        unset($_SESSION['shoppingCart'][$unique_id]);
        $this->updateCart();
        $this->updQtePrice();
        return TRUE;
    }

    /**
     * @return bool
     */
    public function destroy() {
        unset($_SESSION['shoppingCart']);
        $this->shoppingCart = NULL;
        return TRUE;
    }

    /**
     * @desc actualizamos el contenido del carrito
     */
    public function updateCart() {
        self::__construct();
    }
}