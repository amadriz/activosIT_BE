<?php

    //Herencia de la clase padre Mysql
    class EstadosModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getEstados(){
            $sql = "SELECT id_estado, nombre_estado, descripcion, permite_prestamo FROM estado_activos WHERE 1 = 1";


            $request = $this->select_all($sql);
            
            
            return $request;

        }

    }

?>