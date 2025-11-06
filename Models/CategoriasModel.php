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
            $sql = "SELECT id_categoria, nombre_categoria, descripcion FROM categorias WHERE 1 = 1";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;

        }

        public function insertCategoria($strNombre, $strDescripcion){
            $this->strNombre = $strNombre;
            $this->strDescripcion = $strDescripcion;

            $sql = "INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?, ?)";
            $arrData = array($this->strNombre, $this->strDescripcion);
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

        public function updateCategoria(int $idCategoria, string $strNombre, string $strDescripcion){
            $this->idCategoria = $idCategoria;
            $this->strNombre = $strNombre;
            $this->strDescripcion = $strDescripcion;

            $sql = "UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?";
            $arrData = array($this->strNombre, $this->strDescripcion, $this->idCategoria);
            $request_update = $this->update($sql, $arrData);
            return $request_update;
        }

        public function getCategoriaById($id_categoria)
        {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion FROM categorias WHERE id_categoria = ?";
            $request = $this->select($sql, [$id_categoria]);
            return $request;
        }

    }

?>