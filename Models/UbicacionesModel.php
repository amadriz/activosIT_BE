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
            $sql = "SELECT id_ubicacion, edificio, piso, aula_oficina, descripcion FROM ubicaciones WHERE 1 = 1";
            $request = $this->select_all($sql);
            return $request;
        }

        public function insertUbicacion($edificio, $piso, $aula_oficina, $descripcion)
        {
            $sql = "INSERT INTO ubicaciones (edificio, piso, aula_oficina, descripcion) VALUES (?, ?, ?, ?)";
            $arrData = [$edificio, $piso, $aula_oficina, $descripcion];
            $request = $this->insert($sql, $arrData);
            return $request;
        }

        public function updateUbicacion($id_ubicacion, $edificio, $piso, $aula_oficina, $descripcion)
        {
            $sql = "UPDATE ubicaciones SET edificio = ?, piso = ?, aula_oficina = ?, descripcion = ? WHERE id_ubicacion = ?";
            $arrData = [$edificio, $piso, $aula_oficina, $descripcion, $id_ubicacion];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function deleteUbicacion($id_ubicacion)
        {
            $sql = "DELETE FROM ubicaciones WHERE id_ubicacion = ?";
            $request = $this->delete($sql, [$id_ubicacion]);
            return $request;
        }

        public function getUbicacionById($id_ubicacion)
        {
            $sql = "SELECT id_ubicacion, edificio, piso, aula_oficina, descripcion FROM ubicaciones WHERE id_ubicacion = ?";
            $request = $this->select($sql, [$id_ubicacion]);
            return $request;
        }

    }

?>