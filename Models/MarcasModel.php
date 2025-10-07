<?php

    //Herencia de la clase padre Mysql
    class MarcasModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getMarcas(){
            $sql = "SELECT id_marca, nombre_marca, descripcion, estado FROM marca WHERE 1 = 1";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;

        }

    }

?>