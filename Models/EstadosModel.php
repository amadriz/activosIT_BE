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

        public function insertEstado($nombre_estado, $descripcion, $permite_prestamo)
        {
            $sql = "INSERT INTO estado_activos (nombre_estado, descripcion, permite_prestamo) VALUES (?, ?, ?)";
            $arrData = [$nombre_estado, $descripcion, $permite_prestamo];
            $request = $this->insert($sql, $arrData);
            return $request;
        }

        public function updateEstado($id_estado, $nombre_estado, $descripcion, $permite_prestamo)
        {
            $sql = "UPDATE estado_activos SET nombre_estado = ?, descripcion = ?, permite_prestamo = ? WHERE id_estado = ?";
            $arrData = [$nombre_estado, $descripcion, $permite_prestamo, $id_estado];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function deleteEstado($id_estado)
        {
            $sql = "DELETE FROM estado_activos WHERE id_estado = ?";
            $request = $this->delete($sql, [$id_estado]);
            return $request;
        }

        public function getEstadoById($id_estado)
        {
            $sql = "SELECT id_estado, nombre_estado, descripcion, permite_prestamo FROM estado_activos WHERE id_estado = ?";
            $request = $this->select($sql, [$id_estado]);
            return $request;
        }

        // ========== MÉTODOS PARA ESTADO_PRESTAMO ==========

        public function getEstadosPrestamo()
        {
            $sql = "SELECT id_estado_prestamo, nombre_estado, descripcion FROM estado_prestamo WHERE 1 = 1";
            $request = $this->select_all($sql);
            return $request;
        }

        public function insertEstadoPrestamo($nombre_estado, $descripcion)
        {
            $sql = "INSERT INTO estado_prestamo (nombre_estado, descripcion) VALUES (?, ?)";
            $arrData = [$nombre_estado, $descripcion];
            $request = $this->insert($sql, $arrData);
            return $request;
        }

        public function updateEstadoPrestamo($id_estado_prestamo, $nombre_estado, $descripcion)
        {
            $sql = "UPDATE estado_prestamo SET nombre_estado = ?, descripcion = ? WHERE id_estado_prestamo = ?";
            $arrData = [$nombre_estado, $descripcion, $id_estado_prestamo];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function deleteEstadoPrestamo($id_estado_prestamo)
        {
            $sql = "DELETE FROM estado_prestamo WHERE id_estado_prestamo = ?";
            $request = $this->delete($sql, [$id_estado_prestamo]);
            return $request;
        }

        public function getEstadoPrestamoById($id_estado_prestamo)
        {
            $sql = "SELECT id_estado_prestamo, nombre_estado, descripcion FROM estado_prestamo WHERE id_estado_prestamo = ?";
            $request = $this->select($sql, [$id_estado_prestamo]);
            return $request;
        }

    }

?>