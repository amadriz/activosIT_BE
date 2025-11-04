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

        public function insertMarca($nombre_marca, $descripcion, $estado)
        {
            $sql = "INSERT INTO marca (nombre_marca, descripcion, estado) VALUES (?, ?, ?)";
            $arrData = [$nombre_marca, $descripcion, $estado];
            $request = $this->insert($sql, $arrData);
            return $request;
        }

        public function updateMarca($id_marca, $nombre_marca, $descripcion, $estado)
        {
            $sql = "UPDATE marca SET nombre_marca = ?, descripcion = ?, estado = ? WHERE id_marca = ?";
            $arrData = [$nombre_marca, $descripcion, $estado, $id_marca];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function deleteMarca($id_marca)
        {
            $sql = "DELETE FROM marca WHERE id_marca = ?";
            $request = $this->delete($sql, [$id_marca]);
            return $request;
        }

        public function getMarcaById($id_marca)
        {
            $sql = "SELECT id_marca, nombre_marca, descripcion, estado FROM marca WHERE id_marca = ?";
            $request = $this->select($sql, [$id_marca]);
            return $request;
        }

    }

?>