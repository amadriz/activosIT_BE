<?php

    //Herencia de la clase padre Mysql
    class UbicacionesModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getUbicaciones(){
            $sql = "SELECT id_ubicacion, edificio, aula_oficina, descripcion FROM ubicaciones WHERE 1 = 1";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;

        }

    }

?>