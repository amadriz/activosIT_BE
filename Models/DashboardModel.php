<?php

    //Herencia de la clase padre Mysql
    class DashboardModel extends Mysql
    {
        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            parent::__construct();
        }

        // =======================================
        // 1. MÉTRICAS DE ACTIVOS
        // =======================================
        
        /**
         * Obtiene el resumen general de activos
         */
        public function getActivosResumen()
        {
            $sql = "SELECT 
                        COUNT(*) as total_activos,
                        COUNT(CASE WHEN e.nombre_estado = 'Disponible' THEN 1 END) as disponibles,
                        COUNT(CASE WHEN e.nombre_estado = 'En préstamo' THEN 1 END) as en_prestamo,
                        COUNT(CASE WHEN e.nombre_estado = 'En mantenimiento' THEN 1 END) as en_mantenimiento,
                        COUNT(CASE WHEN e.nombre_estado = 'Dañado' THEN 1 END) as danados,
                        COUNT(CASE WHEN e.nombre_estado = 'Baja' THEN 1 END) as dados_baja
                    FROM activos a
                    LEFT JOIN estado_activos e ON a.id_estado = e.id_estado
                    WHERE a.status = 1";
            
            return $this->select($sql);
        }

        /**
         * Obtiene la tendencia mensual de activos agregados (últimos 12 meses)
         */
        public function getTendenciaMensualActivos()
        {
            $sql = "SELECT 
                        DATE_FORMAT(fecha_registro, '%Y-%m') as periodo,
                        DATE_FORMAT(fecha_registro, '%M %Y') as periodo_texto,
                        COUNT(*) as cantidad_agregada
                    FROM activos
                    WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    AND status = 1
                    GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
                    ORDER BY periodo";
            
            return $this->select_all($sql);
        }

        // =======================================
        // 2. MÉTRICAS DE PRÉSTAMOS
        // =======================================
        
        /**
         * Obtiene el resumen de préstamos activos vs pendientes
         */
        public function getPrestamosResumen()
        {
            $sql = "SELECT 
                        COUNT(*) as total_prestamos,
                        COUNT(CASE WHEN ep.nombre_estado = 'Aprobado' AND p.fecha_devolucion IS NULL THEN 1 END) as activos,
                        COUNT(CASE WHEN ep.nombre_estado = 'Pendiente' THEN 1 END) as pendientes_aprobacion,
                        COUNT(CASE WHEN ep.nombre_estado = 'Rechazado' THEN 1 END) as rechazados,
                        COUNT(CASE WHEN ep.nombre_estado = 'Completado' THEN 1 END) as completados
                    FROM prestamos p
                    LEFT JOIN estado_prestamos ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                    WHERE p.status = 1";
            
            return $this->select($sql);
        }

        /**
         * Calcula la tasa de aprobación de préstamos
         */
        public function getTasaAprobacionPrestamos()
        {
            $sql = "SELECT 
                        COUNT(*) as total_solicitudes,
                        COUNT(CASE WHEN ep.nombre_estado = 'Aprobado' THEN 1 END) as aprobados,
                        COUNT(CASE WHEN ep.nombre_estado = 'Rechazado' THEN 1 END) as rechazados,
                        ROUND((COUNT(CASE WHEN ep.nombre_estado = 'Aprobado' THEN 1 END) * 100.0 / COUNT(*)), 2) as tasa_aprobacion
                    FROM prestamos p
                    LEFT JOIN estado_prestamos ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                    WHERE p.status = 1
                    AND ep.nombre_estado IN ('Aprobado', 'Rechazado')";
            
            return $this->select($sql);
        }

        // =======================================
        // 3. MÉTRICAS DE ACTIVIDAD DE USUARIOS
        // =======================================
        
        /**
         * Obtiene los usuarios más activos (que más préstamos solicitan)
         */
        public function getUsuariosMasActivos($limite = 10)
        {
            $sql = "SELECT 
                        u.id_usuario,
                        CONCAT(u.nombre, ' ', u.apellido) as usuario,
                        u.email,
                        u.rol,
                        COUNT(p.id_prestamo) as total_prestamos,
                        COUNT(CASE WHEN ep.nombre_estado = 'Aprobado' THEN 1 END) as prestamos_aprobados,
                        COUNT(CASE WHEN ep.nombre_estado = 'Pendiente' THEN 1 END) as prestamos_pendientes,
                        MAX(p.fecha_solicitud) as ultima_solicitud
                    FROM usuario u
                    LEFT JOIN prestamos p ON u.id_usuario = p.id_usuario_solicitante AND p.status = 1
                    LEFT JOIN estado_prestamos ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                    WHERE u.status = 1
                    GROUP BY u.id_usuario
                    HAVING total_prestamos > 0
                    ORDER BY total_prestamos DESC
                    LIMIT $limite";
            
            return $this->select_all($sql);
        }

        /**
         * Obtiene la distribución de usuarios por rol
         */
        public function getDistribucionUsuariosPorRol()
        {
            $sql = "SELECT 
                        u.rol,
                        COUNT(*) as cantidad_usuarios,
                        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM usuario WHERE status = 1)), 2) as porcentaje,
                        COUNT(CASE WHEN p.id_prestamo IS NOT NULL THEN 1 END) as usuarios_con_prestamos
                    FROM usuario u
                    LEFT JOIN prestamos p ON u.id_usuario = p.id_usuario_solicitante AND p.status = 1
                    WHERE u.status = 1
                    GROUP BY u.rol
                    ORDER BY cantidad_usuarios DESC";
            
            return $this->select_all($sql);
        }

        // =======================================
        // 4. MÉTRICAS DE ACTIVOS MÁS SOLICITADOS
        // =======================================
        
        /**
         * Obtiene el top de activos más prestados
         */
        public function getActivosMasPrestados($limite = 5)
        {
            $sql = "SELECT 
                        a.id_activo,
                        a.codigo_activo,
                        a.nombre_activo,
                        c.nombre_categoria as categoria,
                        m.nombre_marca as marca,
                        COUNT(p.id_prestamo) as total_prestamos,
                        COUNT(CASE WHEN ep.nombre_estado = 'Completado' THEN 1 END) as prestamos_completados,
                        COUNT(CASE WHEN ep.nombre_estado = 'Aprobado' AND p.fecha_devolucion IS NULL THEN 1 END) as prestamos_activos,
                        MAX(p.fecha_solicitud) as ultimo_prestamo,
                        AVG(CASE WHEN p.fecha_entrega IS NOT NULL AND p.fecha_devolucion IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, p.fecha_entrega, p.fecha_devolucion) END) as promedio_horas_uso
                    FROM activos a
                    LEFT JOIN prestamos p ON a.id_activo = p.id_activo AND p.status = 1
                    LEFT JOIN estado_prestamos ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                    LEFT JOIN categorias c ON a.id_categoria = c.id_categoria
                    LEFT JOIN marca m ON a.id_marca = m.id_marca
                    WHERE a.status = 1
                    GROUP BY a.id_activo
                    HAVING total_prestamos > 0
                    ORDER BY total_prestamos DESC
                    LIMIT $limite";
            
            return $this->select_all($sql);
        }
    }

?>