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
                        COUNT(CASE WHEN e.nombre_estado = 'En Mantenimiento' THEN 1 END) as en_mantenimiento,
                        COUNT(CASE WHEN e.nombre_estado = 'No Disponible' THEN 1 END) as no_disponibles
                    FROM activos a
                    LEFT JOIN estado_activos e ON a.id_estado = e.id_estado";
            
            return $this->select($sql, []);
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
                    GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
                    ORDER BY periodo";
            
            return $this->select_all($sql, []);
        }

        // =======================================
        // 2. MÉTRICAS DE PRÉSTAMOS
        // =======================================
        
        /**
         * Obtiene el resumen de préstamos activos vs pendientes
         */
        public function getPrestamosResumen()
        {
            try {
                // Primero, probemos una consulta simple
                $testSql = "SELECT COUNT(*) as count FROM prestamo";
                $testResult = $this->select($testSql, []);
                
                if (!$testResult) {
                    error_log("Error: No se pudo ejecutar consulta básica en tabla prestamo");
                    return ['error' => 'No se pudo acceder a la tabla prestamo', 'test_result' => false];
                }
                
                $sql = "SELECT 
                            COUNT(*) as total_prestamos,
                            SUM(CASE WHEN ep.nombre_estado = 'Aprobado' THEN 1 ELSE 0 END) as aprobados,
                            SUM(CASE WHEN ep.nombre_estado = 'Entregado' THEN 1 ELSE 0 END) as entregados,
                            SUM(CASE WHEN ep.nombre_estado = 'Solicitado' THEN 1 ELSE 0 END) as solicitados,
                            SUM(CASE WHEN ep.nombre_estado = 'Rechazado' THEN 1 ELSE 0 END) as rechazados,
                            SUM(CASE WHEN ep.nombre_estado = 'Devuelto' THEN 1 ELSE 0 END) as devueltos
                        FROM prestamo p
                        LEFT JOIN estado_prestamo ep ON p.id_estado_prestamo = ep.id_estado_prestamo";
                
                error_log("Ejecutando SQL: " . $sql);
                $result = $this->select($sql, []);
                
                if (!$result) {
                    error_log("Error ejecutando consulta de préstamos resumen");
                    return ['error' => 'Error en consulta SQL', 'sql' => $sql, 'test_count' => $testResult['count']];
                }
                
                return $result;
                
            } catch (Exception $e) {
                error_log("Exception en getPrestamosResumen: " . $e->getMessage());
                return ['error' => $e->getMessage(), 'method' => 'getPrestamosResumen'];
            }
        }

        /**
         * Calcula la tasa de aprobación de préstamos
         */
        public function getTasaAprobacionPrestamos()
        {
            try {
                // Primero verificar que las tablas existan
                $testSql = "SELECT ep.nombre_estado, COUNT(*) as count 
                           FROM prestamo p 
                           LEFT JOIN estado_prestamo ep ON p.id_estado_prestamo = ep.id_estado_prestamo 
                           GROUP BY ep.nombre_estado";
                
                error_log("Test SQL para tasa de aprobación: " . $testSql);
                $testResult = $this->select_all($testSql, []);
                
                if (!$testResult) {
                    return ['error' => 'No se pudo ejecutar consulta de test', 'sql' => $testSql];
                }
                
                // Consulta principal simplificada
                $sql = "SELECT 
                            COUNT(*) as total_solicitudes,
                            SUM(CASE WHEN ep.nombre_estado = 'Aprobado' THEN 1 ELSE 0 END) as aprobados,
                            SUM(CASE WHEN ep.nombre_estado = 'Rechazado' THEN 1 ELSE 0 END) as rechazados
                        FROM prestamo p
                        LEFT JOIN estado_prestamo ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                        WHERE ep.nombre_estado IN ('Aprobado', 'Rechazado')";
                
                error_log("Ejecutando SQL tasa aprobación: " . $sql);
                $result = $this->select($sql, []);
                
                if (!$result) {
                    return ['error' => 'Error en consulta principal', 'test_states' => $testResult];
                }
                
                // Calcular tasa de aprobación
                if ($result['total_solicitudes'] > 0) {
                    $result['tasa_aprobacion'] = round(($result['aprobados'] * 100.0 / $result['total_solicitudes']), 2);
                } else {
                    $result['tasa_aprobacion'] = 0;
                }
                
                return $result;
                
            } catch (Exception $e) {
                error_log("Exception en getTasaAprobacionPrestamos: " . $e->getMessage());
                return ['error' => $e->getMessage(), 'method' => 'getTasaAprobacionPrestamos'];
            }
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
                        COUNT(CASE WHEN ep.nombre_estado = 'Solicitado' THEN 1 END) as prestamos_pendientes,
                        MAX(p.fecha_solicitud) as ultima_solicitud
                    FROM usuario u
                    LEFT JOIN prestamo p ON u.id_usuario = p.id_usuario
                    LEFT JOIN estado_prestamo ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                    WHERE u.status = 1
                    GROUP BY u.id_usuario
                    HAVING total_prestamos > 0
                    ORDER BY total_prestamos DESC
                    LIMIT $limite";
            
            return $this->select_all($sql, []);
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
                        COUNT(DISTINCT p.id_usuario) as usuarios_con_prestamos
                    FROM usuario u
                    LEFT JOIN prestamo p ON u.id_usuario = p.id_usuario
                    WHERE u.status = 1
                    GROUP BY u.rol
                    ORDER BY cantidad_usuarios DESC";
            
            return $this->select_all($sql, []);
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
                        COUNT(CASE WHEN ep.nombre_estado = 'Devuelto' THEN 1 END) as prestamos_completados,
                        COUNT(CASE WHEN ep.nombre_estado IN ('Aprobado', 'Entregado') THEN 1 END) as prestamos_activos,
                        MAX(p.fecha_solicitud) as ultimo_prestamo,
                        AVG(CASE WHEN p.fecha_entrega IS NOT NULL AND p.fecha_devolucion IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, p.fecha_entrega, p.fecha_devolucion) END) as promedio_horas_uso
                    FROM activos a
                    LEFT JOIN prestamo p ON a.id_activo = p.id_activo
                    LEFT JOIN estado_prestamo ep ON p.id_estado_prestamo = ep.id_estado_prestamo
                    LEFT JOIN categorias c ON a.id_categoria = c.id_categoria
                    LEFT JOIN marca m ON a.id_marca = m.id_marca
                    GROUP BY a.id_activo
                    HAVING total_prestamos > 0
                    ORDER BY total_prestamos DESC
                    LIMIT $limite";
            
            return $this->select_all($sql, []);
        }
    }

?>