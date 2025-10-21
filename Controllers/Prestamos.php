<?php
    class Prestamos extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function fetchPrestamos()
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                
                if ($method !== 'GET') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos GET"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                $datos = $this->model->getPrestamos();

                if (empty($datos)) {
                    $response = [
                        "status" => false,
                        "message" => "No hay préstamos registrados"
                    ];
                } else {
                    $response = [
                        "status" => true,
                        "message" => "Préstamos encontrados",
                        "data" => $datos
                    ];
                }

                jsonResponse($response, 200);

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
        }

        //3 etapas para prestamos solicitud de préstamo, aprobación y entrega, devolución y cierre

        public function solicitudPrestamo()
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                
                if ($method !== 'POST') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos POST"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                // Obtener y validar los datos del cuerpo de la solicitud
                $input = json_decode(file_get_contents('php://input'), true);

                // Debug: verificar qué datos estamos recibiendo
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $response = [
                        "status" => false,
                        "message" => "Error en el formato JSON: " . json_last_error_msg()
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                if (!isset($input['id_usuario'], $input['id_activo'], $input['fecha_inicio_solicitada'], $input['fecha_fin_solicitada'], $input['proposito'])) {
                    $response = [
                        "status" => false,
                        "message" => "Faltan datos obligatorios: id_usuario, id_activo, fecha_inicio_solicitada, fecha_fin_solicitada, proposito"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $id_usuario = intval($input['id_usuario']);
                $id_activo = intval($input['id_activo']);
                $fecha_inicio_solicitada = $input['fecha_inicio_solicitada'];
                $fecha_fin_solicitada = $input['fecha_fin_solicitada'];
                $proposito = trim($input['proposito']);
                $id_ubicacion = isset($input['id_ubicacion']) && !empty($input['id_ubicacion']) ? intval($input['id_ubicacion']) : null;

                // Validaciones básicas
                if ($id_usuario <= 0 || $id_activo <= 0 || empty($fecha_inicio_solicitada) || empty($fecha_fin_solicitada) || empty($proposito)) {
                    $response = [
                        "status" => false,
                        "message" => "Datos inválidos: IDs deben ser positivos y campos de texto no pueden estar vacíos"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar formato de fechas y horas
                // Acepta: YYYY-MM-DD HH:MM:SS o YYYY-MM-DD HH:MM
                $patron_fecha = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/';
                
                if (!preg_match($patron_fecha, $fecha_inicio_solicitada)) {
                    $response = [
                        "status" => false,
                        "message" => "Formato de fecha y hora de inicio inválido. Recibido: '" . $fecha_inicio_solicitada . "'. Use YYYY-MM-DD HH:MM:SS"
                    ];
                    jsonResponse($response, 400);
                    return;
                }
                
                if (!preg_match($patron_fecha, $fecha_fin_solicitada)) {
                    $response = [
                        "status" => false,
                        "message" => "Formato de fecha y hora de fin inválido. Recibido: '" . $fecha_fin_solicitada . "'. Use YYYY-MM-DD HH:MM:SS"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar que las fechas sean válidas usando DateTime (más robusto)
                try {
                    // Forzar zona horaria para evitar problemas
                    $timezone = new DateTimeZone('America/Costa_Rica'); // Ajusta según tu zona horaria
                    
                    $fecha_inicio_obj = new DateTime($fecha_inicio_solicitada, $timezone);
                    $fecha_fin_obj = new DateTime($fecha_fin_solicitada, $timezone);
                    $fecha_actual_obj = new DateTime('now', $timezone);
                    
                    $fecha_inicio = $fecha_inicio_obj->getTimestamp();
                    $fecha_fin = $fecha_fin_obj->getTimestamp();
                    $fecha_actual = $fecha_actual_obj->getTimestamp();
                    
                } catch (Exception $e) {
                    $response = [
                        "status" => false,
                        "message" => "Fechas y horas inválidas. Error: " . $e->getMessage()
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar que la fecha de inicio no sea mayor a la fecha de fin
                if ($fecha_inicio > $fecha_fin) {
                    $response = [
                        "status" => false,
                        "message" => "La fecha y hora de inicio no puede ser mayor a la fecha y hora de fin",
                        "debug" => [
                            "fecha_inicio_recibida" => $fecha_inicio_solicitada,
                            "fecha_fin_recibida" => $fecha_fin_solicitada,
                            "fecha_inicio_timestamp" => $fecha_inicio,
                            "fecha_fin_timestamp" => $fecha_fin,
                            "fecha_inicio_formateada" => date('Y-m-d H:i:s', $fecha_inicio),
                            "fecha_fin_formateada" => date('Y-m-d H:i:s', $fecha_fin)
                        ]
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar duración mínima de 1 hora
                $duracion_horas = ($fecha_fin - $fecha_inicio) / 3600; // Convertir segundos a horas
                if ($duracion_horas < 1) {
                    $response = [
                        "status" => false,
                        "message" => "La duración mínima del préstamo debe ser de 1 hora"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar que las fechas no sean en el pasado (con tolerancia de 1 hora)
                if ($fecha_inicio < ($fecha_actual - 3600)) {
                    $response = [
                        "status" => false,
                        "message" => "La fecha y hora de inicio no puede ser anterior a la hora actual"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar que el usuario existe y está activo
                if (!$this->model->validarUsuarioActivo($id_usuario)) {
                    $response = [
                        "status" => false,
                        "message" => "Usuario no encontrado o inactivo"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar que el activo existe y permite préstamos
                if (!$this->model->validarActivoDisponible($id_activo)) {
                    $response = [
                        "status" => false,
                        "message" => "Activo no encontrado o no disponible para préstamo"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el activo no tenga préstamos activos
                if ($this->model->verificarPrestamoActivo($id_activo)) {
                    $response = [
                        "status" => false,
                        "message" => "El activo ya tiene un préstamo activo"
                    ];
                    jsonResponse($response, 409); // Conflict
                    return;
                }

                // Si todas las validaciones pasan, crear la solicitud de préstamo
                $prestamo_id = $this->model->crearPrestamo($id_usuario, $id_activo, $fecha_inicio_solicitada, $fecha_fin_solicitada, $proposito, $id_ubicacion);

                if ($prestamo_id > 0) {
                    $response = [
                        "status" => true,
                        "message" => "Solicitud de préstamo creada con éxito",
                        "data" => ["id_prestamo" => $prestamo_id]
                    ];
                    jsonResponse($response, 201);
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al crear la solicitud de préstamo"
                    ];
                    jsonResponse($response, 500);
                }

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
        }

        public function aprobarPrestamo($id_prestamo = null)
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                
                if ($method !== 'PUT' && $method !== 'PATCH') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos PUT o PATCH"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                // Validar que se proporcionó el ID del préstamo
                if (!$id_prestamo || intval($id_prestamo) <= 0) {
                    $response = [
                        "status" => false,
                        "message" => "ID de préstamo requerido y debe ser válido"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $id_prestamo = intval($id_prestamo);

                // Obtener y validar los datos del cuerpo de la solicitud
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($input['accion'], $input['usuario_aprobador'])) {
                    $response = [
                        "status" => false,
                        "message" => "Faltan datos obligatorios: accion, usuario_aprobador"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $accion = strtolower(trim($input['accion']));
                $usuario_aprobador = intval($input['usuario_aprobador']);
                $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : null;

                // Validar acción
                if (!in_array($accion, ['aprobar', 'rechazar'])) {
                    $response = [
                        "status" => false,
                        "message" => "Acción inválida. Use 'aprobar' o 'rechazar'"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar usuario aprobador
                if ($usuario_aprobador <= 0) {
                    $response = [
                        "status" => false,
                        "message" => "ID de usuario aprobador inválido"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el usuario aprobador existe, está activo y tiene rol admin
                if (!$this->model->validarUsuarioAdmin($usuario_aprobador)) {
                    $response = [
                        "status" => false,
                        "message" => "Usuario aprobador no encontrado, inactivo o no tiene permisos de administrador"
                    ];
                    jsonResponse($response, 403); // Forbidden
                    return;
                }

                // Verificar que el préstamo existe
                $prestamo = $this->model->obtenerPrestamoPorId($id_prestamo);
                if (empty($prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "Préstamo no encontrado"
                    ];
                    jsonResponse($response, 404);
                    return;
                }

                // Verificar que el préstamo esté en estado 'Solicitado' para poder aprobarlo/rechazarlo
                if (!$this->model->validarPrestamoParaAprobacion($id_prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "El préstamo no está en estado 'Solicitado' y no puede ser procesado"
                    ];
                    jsonResponse($response, 409); // Conflict
                    return;
                }

                // Procesar la aprobación/rechazo
                $resultado = $this->model->aprobarRechazarPrestamo($id_prestamo, $accion, $usuario_aprobador, $observaciones);

                if ($resultado) {
                    $mensaje = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';
                    $response = [
                        "status" => true,
                        "message" => "Préstamo {$mensaje} exitosamente",
                        "data" => [
                            "id_prestamo" => $id_prestamo,
                            "accion" => $accion,
                            "usuario_aprobador" => $usuario_aprobador,
                            "observaciones" => $observaciones
                        ]
                    ];
                    jsonResponse($response, 200);
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al procesar la solicitud de préstamo"
                    ];
                    jsonResponse($response, 500);
                }

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
        }

        public function entregarPrestamo($id_prestamo = null)
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                
                if ($method !== 'PUT' && $method !== 'PATCH') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos PUT o PATCH"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                // Validar que se proporcionó el ID del préstamo
                if (!$id_prestamo || intval($id_prestamo) <= 0) {
                    $response = [
                        "status" => false,
                        "message" => "ID de préstamo requerido y debe ser válido"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $id_prestamo = intval($id_prestamo);

                // Obtener y validar los datos del cuerpo de la solicitud
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($input['usuario_entregador'])) {
                    $response = [
                        "status" => false,
                        "message" => "Faltan datos obligatorios: usuario_entregador"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $usuario_entregador = intval($input['usuario_entregador']);
                $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : null;

                // Validar usuario entregador
                if ($usuario_entregador <= 0) {
                    $response = [
                        "status" => false,
                        "message" => "ID de usuario entregador inválido"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el usuario entregador existe y está activo
                if (!$this->model->validarUsuarioActivo($usuario_entregador)) {
                    $response = [
                        "status" => false,
                        "message" => "Usuario entregador no encontrado o inactivo"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el préstamo existe
                $prestamo = $this->model->obtenerPrestamoPorId($id_prestamo);
                if (empty($prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "Préstamo no encontrado"
                    ];
                    jsonResponse($response, 404);
                    return;
                }
                // Verificar que el préstamo esté en estado 'Aprobado' para poder entregarlo
                if ($prestamo['id_estado_prestamo'] != 2) { // Estado 2 = Aprobado
                    $response = [
                        "status" => false,
                        "message" => "El préstamo no está en estado 'Aprobado' y no puede ser entregado"
                    ];
                    jsonResponse($response, 409); // Conflict
                    return;
                }

                // Procesar la entrega del préstamo
                $resultado = $this->model->entregarPrestamo($id_prestamo, $usuario_entregador, $observaciones);

                if ($resultado) {
                    $response = [
                        "status" => true,
                        "message" => "Préstamo entregado exitosamente",
                        "data" => [
                            "id_prestamo" => $id_prestamo,
                            "usuario_entregador" => $usuario_entregador,
                            "observaciones" => $observaciones
                        ]
                    ];
                    jsonResponse($response, 200);
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al procesar la entrega del préstamo"
                    ];
                    jsonResponse($response, 500);
                }

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
        }

        public function devolverPrestamo($id_prestamo = null)
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                
                if ($method !== 'PUT' && $method !== 'PATCH') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos PUT o PATCH"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                // Validar que se proporcionó el ID del préstamo
                if (!$id_prestamo || intval($id_prestamo) <= 0) {
                    $response = [
                        "status" => false,
                        "message" => "ID de préstamo requerido y debe ser válido"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $id_prestamo = intval($id_prestamo);

                // Obtener y validar los datos del cuerpo de la solicitud
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($input['usuario_recibe'])) {
                    $response = [
                        "status" => false,
                        "message" => "Faltan datos obligatorios: usuario_recibe"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                $usuario_recibe = intval($input['usuario_recibe']);
                $calificacion = isset($input['calificacion']) && !empty($input['calificacion']) ? intval($input['calificacion']) : null;
                $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : null;

                // Validar usuario que recibe
                if ($usuario_recibe <= 0) {
                    $response = [
                        "status" => false,
                        "message" => "ID de usuario que recibe inválido"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Validar calificación si se proporciona (1-5)
                if ($calificacion !== null && ($calificacion < 1 || $calificacion > 10)) {
                    $response = [
                        "status" => false,
                        "message" => "Calificación debe estar entre 1 y 10"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el usuario que recibe existe y está activo
                if (!$this->model->validarUsuarioActivo($usuario_recibe)) {
                    $response = [
                        "status" => false,
                        "message" => "Usuario que recibe no encontrado o inactivo"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el préstamo existe
                $prestamo = $this->model->obtenerPrestamoPorId($id_prestamo);
                if (empty($prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "Préstamo no encontrado"
                    ];
                    jsonResponse($response, 404);
                    return;
                }

                // Verificar que el préstamo esté en estado 'Entregado' para poder devolverlo
                if (!$this->model->validarPrestamoParaDevolucion($id_prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "El préstamo no está en estado 'Entregado' y no puede ser devuelto"
                    ];
                    jsonResponse($response, 409); // Conflict
                    return;
                }

                // Procesar la devolución del préstamo
                $resultado = $this->model->devolverPrestamo($id_prestamo, $usuario_recibe, $calificacion, $observaciones);

                if ($resultado) {
                    $response = [
                        "status" => true,
                        "message" => "Préstamo devuelto exitosamente",
                        "data" => [
                            "id_prestamo" => $id_prestamo,
                            "usuario_recibe" => $usuario_recibe,
                            "calificacion" => $calificacion,
                            "observaciones" => $observaciones
                        ]
                    ];
                    jsonResponse($response, 200);
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al procesar la devolución del préstamo"
                    ];
                    jsonResponse($response, 500);
                }

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
        }

        public function estado_Prestamo(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                
                if ($method !== 'GET') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos GET"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                $datos = $this->model->getEstado_Prestamo();

                if (empty($datos)) {
                    $response = [
                        "status" => false,
                        "message" => "No hay estados de préstamos registrados"
                    ];
                } else {
                    $response = [
                        "status" => true,
                        "message" => "Estados de préstamos encontrados",
                        "data" => $datos
                    ];
                }

                jsonResponse($response, 200);

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
            
        }

        public function eliminarPrestamo($id_prestamo = null)
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];

                if ($method !== 'DELETE') {
                    $response = [
                        "status" => false,
                        "message" => "Error: solo se permiten métodos DELETE"
                    ];
                    jsonResponse($response, 405);
                    return;
                }

                if (is_null($id_prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "ID de préstamo no proporcionado"
                    ];
                    jsonResponse($response, 400);
                    return;
                }

                // Verificar que el préstamo existe
                $prestamo = $this->model->obtenerPrestamoPorId($id_prestamo);
                if (empty($prestamo)) {
                    $response = [
                        "status" => false,
                        "message" => "Préstamo no encontrado"
                    ];
                    jsonResponse($response, 404);
                    return;
                }

                // Procesar la eliminación del préstamo
                $resultado = $this->model->eliminarPrestamo($id_prestamo);

                if ($resultado) {
                    $response = [
                        "status" => true,
                        "message" => "Préstamo eliminado exitosamente"
                    ];
                    jsonResponse($response, 200);
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al procesar la eliminación del préstamo"
                    ];
                    jsonResponse($response, 500);
                }

            } catch (Exception $e) {
                $response = [
                    "status" => false,
                    "message" => "Error interno del servidor: " . $e->getMessage()
                ];
                jsonResponse($response, 500);
            }
        }



    } //clase
?>