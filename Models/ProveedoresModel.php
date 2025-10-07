<?php

    //Herencia de la clase padre Mysql
    class ProveedoresModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getProveedores(){
            $sql = "SELECT id_proveedor, nombre_proveedor, contacto, telefono, email, direccion FROM proveedores WHERE 1 = 1";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;

        }

    }

?>