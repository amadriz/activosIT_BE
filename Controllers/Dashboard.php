<?php

    class Dashboard extends Controllers
    {
        
        public function __construct()
        {
            // Llamamos al constructor de la clase padre
            parent::__construct();
        }

        // =======================================
        // ENDPOINTS ESPECÍFICOS PARA ACTIVOS
        // =======================================
        
        /**
         * GET /dashboard/activos/resumen
         * Resumen general de activos
         */
        public function activosResumen()
        {
            $arrData = $this->model->getActivosResumen();
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }

        /**
         * GET /dashboard/activos/tendencia
         * Tendencia mensual de activos agregados
         */
        public function activosTendencia()
        {
            $arrData = $this->model->getTendenciaMensualActivos();
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }

        // =======================================
        // ENDPOINTS ESPECÍFICOS PARA PRÉSTAMOS
        // =======================================
        
        /**
         * GET /dashboard/prestamos/resumen
         * Resumen de préstamos activos vs pendientes
         */
        public function prestamosResumen()
        {
            $arrData = $this->model->getPrestamosResumen();
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }

        /**
         * GET /dashboard/prestamos/tasa-aprobacion
         * Tasa de aprobación de préstamos
         */
        public function prestamosTasaAprobacion()
        {
            $arrData = $this->model->getTasaAprobacionPrestamos();
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }

        // =======================================
        // ENDPOINTS ESPECÍFICOS PARA USUARIOS
        // =======================================
        
        /**
         * GET /dashboard/usuarios/mas-activos?limite=10
         * Usuarios más activos
         */
        public function usuariosMasActivos()
        {
            $limite = $_GET['limite'] ?? 10;
            $limite = intval($limite);
            $arrData = $this->model->getUsuariosMasActivos($limite);
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }

        /**
         * GET /dashboard/usuarios/distribucion-roles
         * Distribución de usuarios por rol
         */
        public function usuariosDistribucionRoles()
        {
            $arrData = $this->model->getDistribucionUsuariosPorRol();
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }

        // =======================================
        // ENDPOINTS PARA ACTIVOS MÁS SOLICITADOS
        // =======================================
        
        /**
         * GET /dashboard/activos-populares/mas-prestados?limite=5
         * Top activos más prestados
         */
        public function activosMasPrestados()
        {
            $limite = $_GET['limite'] ?? 5;
            $limite = intval($limite);
            $arrData = $this->model->getActivosMasPrestados($limite);
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

?>