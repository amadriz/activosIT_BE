<?php

    //Herencia de la clase padre Mysql
    class PrestamosModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        public function getPrestamos()
        {
            $sql = "SELECT
                p.id_prestamo,
                p.fecha_solicitud,
                p.fecha_inicio_solicitada,
                p.fecha_fin_solicitada,
                p.proposito,
                ep.nombre_estado AS estado_prestamo,
                
                u.id_usuario AS id_usuario_solicitante,
                CONCAT(u.nombre, ' ', u.apellido) AS solicitante,
                
                a.id_activo,
                a.codigo_activo,
                a.nombre_activo,
                c.nombre_categoria AS categoria,
                m.nombre_marca AS marca,
                
                CONCAT_WS(' - ', ub.edificio, ub.piso, ub.aula_oficina) AS ubicacion,
                
                p.fecha_aprobacion,
                CONCAT(uap.nombre, ' ', uap.apellido) AS usuario_aprobador,
                p.fecha_entrega,
                CONCAT(uent.nombre, ' ', uent.apellido) AS usuario_entrega,
                p.fecha_devolucion,
                CONCAT(urec.nombre, ' ', urec.apellido) AS usuario_recibe,
                
                p.calificacion_prestamo,
                p.observaciones_aprobacion,
                p.observaciones_devolucion,
                
                CASE
                    WHEN p.fecha_entrega IS NOT NULL THEN DATEDIFF(COALESCE(p.fecha_devolucion, NOW()), p.fecha_entrega)
                    WHEN p.fecha_inicio_solicitada IS NOT NULL THEN DATEDIFF(COALESCE(p.fecha_devolucion, NOW()), p.fecha_inicio_solicitada)
                    ELSE NULL
                END AS dias_transcurridos,
                
                CASE
                    WHEN p.fecha_fin_solicitada IS NOT NULL
                         AND p.fecha_fin_solicitada < NOW()
                         AND (p.fecha_devolucion IS NULL OR p.id_estado_prestamo <> 4) THEN 'Vencido'
                    ELSE 'OK'
                END AS estado_vencimiento

            FROM prestamo p
            LEFT JOIN estado_prestamo ep ON p.id_estado_prestamo = ep.id_estado_prestamo
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
            LEFT JOIN usuario uap ON p.usuario_aprobador = uap.id_usuario
            LEFT JOIN usuario uent ON p.usuario_entrega = uent.id_usuario
            LEFT JOIN usuario urec ON p.usuario_recibe = urec.id_usuario
            LEFT JOIN activos a ON p.id_activo = a.id_activo
            LEFT JOIN categorias c ON a.id_categoria = c.id_categoria
            LEFT JOIN marca m ON a.id_marca = m.id_marca
            LEFT JOIN ubicaciones ub ON p.id_ubicacion = ub.id_ubicacion
            ORDER BY p.fecha_solicitud DESC, p.id_prestamo DESC";
            
            $request = $this->select_all($sql);
            return $request;
        }

        public function validarUsuarioActivo($id_usuario)
        {
            $sql = "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND status = 1";
            $request = $this->select($sql, [$id_usuario]);
            return !empty($request);
        }

        public function validarActivoDisponible($id_activo)
        {
            $sql = "SELECT a.id_activo, ea.permite_prestamo 
                    FROM activos a 
                    INNER JOIN estado_activos ea ON a.id_estado = ea.id_estado 
                    WHERE a.id_activo = ? AND ea.permite_prestamo = 1";
            $request = $this->select($sql, [$id_activo]);
            return !empty($request);
        }

        public function verificarPrestamoActivo($id_activo)
        {
            $sql = "SELECT id_prestamo FROM prestamo 
                    WHERE id_activo = ? 
                    AND id_estado_prestamo IN (1, 2, 3)"; // Estados: solicitado, aprobado, entregado
            $request = $this->select($sql, [$id_activo]);
            return !empty($request);
        }

        public function crearPrestamo($id_usuario, $id_activo, $fecha_inicio_solicitada, $fecha_fin_solicitada, $proposito, $id_ubicacion = null)
        {
            $sql = "INSERT INTO prestamo (
                        id_usuario, 
                        id_activo, 
                        id_estado_prestamo, 
                        id_ubicacion,
                        fecha_solicitud, 
                        fecha_inicio_solicitada, 
                        fecha_fin_solicitada, 
                        proposito
                    ) VALUES (?, ?, 1, ?, NOW(), ?, ?, ?)"; // Estado 1 = Solicitado
            
            $arrData = [
                $id_usuario,
                $id_activo,
                $id_ubicacion,
                $fecha_inicio_solicitada,
                $fecha_fin_solicitada,
                $proposito
            ];
            
            $request = $this->insert($sql, $arrData);
            return $request;
        }

        public function obtenerPrestamoPorId($id_prestamo)
        {
            $sql = "SELECT p.*, u.nombre, u.apellido, a.codigo_activo, a.nombre_activo
                    FROM prestamo p
                    INNER JOIN usuario u ON p.id_usuario = u.id_usuario
                    INNER JOIN activos a ON p.id_activo = a.id_activo
                    WHERE p.id_prestamo = ?";
            $request = $this->select($sql, [$id_prestamo]);
            return $request;
        }

        public function validarPrestamoParaAprobacion($id_prestamo)
        {
            $sql = "SELECT id_prestamo FROM prestamo 
                    WHERE id_prestamo = ? AND id_estado_prestamo = 1"; // Estado 1 = Solicitado
            $request = $this->select($sql, [$id_prestamo]);
            return !empty($request);
        }

        public function aprobarRechazarPrestamo($id_prestamo, $accion, $usuario_aprobador, $observaciones = null)
        {
            // Estado 2 = Aprobado, Estado 5 = Rechazado (asumiendo que existe)
            $estado = ($accion === 'aprobar') ? 2 : 5;
            
            $sql = "UPDATE prestamo SET 
                        id_estado_prestamo = ?, 
                        fecha_aprobacion = NOW(), 
                        usuario_aprobador = ?,
                        observaciones_aprobacion = ?,
                        updated_at = NOW()
                    WHERE id_prestamo = ?";
            
            $arrData = [$estado, $usuario_aprobador, $observaciones, $id_prestamo];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function entregarPrestamo($id_prestamo, $usuario_entrega, $observaciones = null)
        {
            // Estado 3 = Entregado
            // Nota: Si necesitas un campo específico para observaciones de entrega, 
            // puedes agregar una columna 'observaciones_entrega' a la tabla
            $sql = "UPDATE prestamo SET 
                        id_estado_prestamo = 3, 
                        fecha_entrega = NOW(), 
                        usuario_entrega = ?,
                        updated_at = NOW()
                    WHERE id_prestamo = ?";
            
            $arrData = [$usuario_entrega, $id_prestamo];
            $request = $this->update($sql, $arrData);
            return $request;
        }

        public function validarPrestamoParaDevolucion($id_prestamo)
        {
            $sql = "SELECT id_prestamo FROM prestamo 
                    WHERE id_prestamo = ? AND id_estado_prestamo = 3"; // Estado 3 = Entregado
            $request = $this->select($sql, [$id_prestamo]);
            return !empty($request);
        }

        public function devolverPrestamo($id_prestamo, $usuario_recibe, $calificacion = null, $observaciones = null)
        {
            // Estado 4 = Devuelto/Completado
            $sql = "UPDATE prestamo SET 
                        id_estado_prestamo = 4, 
                        fecha_devolucion = NOW(), 
                        usuario_recibe = ?,
                        calificacion_prestamo = ?,
                        observaciones_devolucion = ?,
                        updated_at = NOW()
                    WHERE id_prestamo = ?";
            
            $arrData = [$usuario_recibe, $calificacion, $observaciones, $id_prestamo];
            $request = $this->update($sql, $arrData);
            return $request;
        }

    }

?>