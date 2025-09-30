<?php

    //Herencia de la clase padre Mysql
    class ActivosModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getActivos(){
            $sql = "SELECT
              a.id_activo,
              a.nombre_activo,
              c.nombre_categoria   AS categoria,
              m.nombre_marca       AS marca,
              CONCAT_WS(' - ', u.edificio, u.piso, u.aula_oficina) AS ubicacion,
              e.nombre_estado      AS estado_activo,
              e.permite_prestamo   AS permite_prestamo,
              CASE WHEN e.permite_prestamo = 1 THEN 'Disponible' ELSE 'No disponible' END AS disponibilidad,
              a.modelo,
              a.numero_serie,
              a.costo_adquisicion,
              a.fecha_garantia_fin,
              a.fecha_registro,
              a.usuario_registro
            FROM activos a
            LEFT JOIN Categorias c     ON a.id_categoria = c.id_categoria
            LEFT JOIN Marca m          ON a.id_marca     = m.id_marca
            LEFT JOIN Ubicaciones u    ON a.id_ubicacion = u.id_ubicacion
            LEFT JOIN Estado_Activos e ON a.id_estado    = e.id_estado
            WHERE 1 = 1";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;

        }

        public function insertActivos($nombre_activo, $id_categoria, $id_marca, $id_ubicacion, $id_estado, $modelo, $numero_serie, $fecha_adquisicion, $costo_adquisicion, $fecha_garantia_inicio, $fecha_garantia_fin, $especificaciones_tecnicas, $observaciones, $usuario_registro){
            
            $sql = "INSERT INTO activos (
                        nombre_activo,
                        id_categoria,
                        id_marca,
                        id_ubicacion,
                        id_estado,
                        modelo,
                        numero_serie,
                        fecha_adquisicion,
                        costo_adquisicion,
                        fecha_garantia_inicio,
                        fecha_garantia_fin,
                        especificaciones_tecnicas,
                        observaciones,
                        usuario_registro,
                        fecha_registro
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $arrData = [
                $nombre_activo,
                $id_categoria,
                $id_marca,
                $id_ubicacion,
                $id_estado,
                $modelo,
                $numero_serie,
                $fecha_adquisicion,
                $costo_adquisicion,
                $fecha_garantia_inicio,
                $fecha_garantia_fin,
                $especificaciones_tecnicas,
                $observaciones,
                $usuario_registro
            ];
            
            $request = $this->insert($sql, $arrData);
            
            return $request;
        }

        public function deleteActivo(int $id){
            $this->intId = $id;

            $sql = "DELETE FROM activos WHERE id_activo = ?";
            $arrData = [$this->intId];
            $request = $this->delete($sql, $arrData);
            return $request;
        }
    }

?>