<?php

    //Herencia de la clase padre Mysql
    class CategoriasModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getCategorias(){
            $sql = "SELECT id_categoria, nombre_categoria, descripcion, estado FROM categorias WHERE 1 = 1";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;

        }

    }

?>