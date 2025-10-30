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

        public function insertCategoria($strNombre, $strDescripcion, $intEstado){
            $this->strNombre = $strNombre;
            $this->strDescripcion = $strDescripcion;
            $this->intEstado = $intEstado;

            $sql = "INSERT INTO categorias (nombre_categoria, descripcion, estado) VALUES (?, ?, ?)";
            $arrData = array($this->strNombre, $this->strDescripcion, $this->intEstado);
            $request_insert = $this->insert($sql, $arrData);
            return $request_insert;
        }

        public function deleteCategoria(int $idCategoria){
            $this->idCategoria = $idCategoria;

            $sql = "DELETE FROM categorias WHERE id_categoria = ?";
            $arrData = array($this->idCategoria);
            $request_delete = $this->delete($sql, $arrData);
            return $request_delete;
        }

    }

?>