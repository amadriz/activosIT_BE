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

        public function insertProveedor($nombre_proveedor, $contacto, $telefono, $email, $direccion)
        {
            $sql = "INSERT INTO proveedores (nombre_proveedor, contacto, telefono, email, direccion) VALUES (?, ?, ?, ?, ?)";
            $arrData = [$nombre_proveedor, $contacto, $telefono, $email, $direccion];
            $request = $this->insert($sql, $arrData);
            return $request;
        }

        public function updateProveedor($id_proveedor, $nombre_proveedor, $contacto, $telefono, $email, $direccion)
        {
            $sql = "UPDATE proveedores SET nombre_proveedor = ?, contacto = ?, telefono = ?, email = ?, direccion = ? WHERE id_proveedor = ?";
            $arrData = [$nombre_proveedor, $contacto, $telefono, $email, $direccion, $id_proveedor];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function deleteProveedor($id_proveedor)
        {
            $sql = "DELETE FROM proveedores WHERE id_proveedor = ?";
            $request = $this->delete($sql, [$id_proveedor]);
            return $request;
        }

        public function getProveedorById($id_proveedor)
        {
            $sql = "SELECT id_proveedor, nombre_proveedor, contacto, telefono, email, direccion FROM proveedores WHERE id_proveedor = ?";
            $request = $this->select($sql, [$id_proveedor]);
            return $request;
        }

    }

?>