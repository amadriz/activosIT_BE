<?php
    class Estados extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function fetchEstados()
    {
        try
        {

            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'GET') {

                $datos = $this->model->getEstados();

                if (empty($datos)) {
                    $response = [
                        "status" => false,
                        "message" => "No hay registros en la base de datos",
                    ];

                    $code = 200;
                    jsonResponse($response, $code);
                    die();
                } else {
                    $response = array(
                        "status" => true,
                        "message" => "Datos encontrados",
                        "data" => $datos,
                    );
                    $code = 200;
                }

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al consultar solo se permiten metodos GET",
                ];

                $code = 200;

                jsonResponse($response, $code);
            }

            $code = 200;

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function agregarEstado()
    {
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if($method == 'POST') {
                
                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_estado']) || !testString($datos['nombre_estado'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre del estado es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['descripcion']) || !testString($datos['descripcion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La descripción es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(!isset($datos['permite_prestamo'])) {
                    $response = [
                        "status" => false,
                        "message" => "El campo permite_prestamo es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                // Validar que permite_prestamo sea válido (0 o 1, true o false)
                $valoresValidos = ['0', '1', 0, 1, true, false];
                if(!in_array($datos['permite_prestamo'], $valoresValidos, true)) {
                    $response = [
                        "status" => false,
                        "message" => "El campo permite_prestamo debe ser 0, 1, true o false",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $strNombre = ucwords(strClean($datos['nombre_estado']));
                $strDescripcion = strClean($datos['descripcion']);
                
                // Convertir permite_prestamo a entero (true/1 = 1, false/0 = 0)
                if($datos['permite_prestamo'] === '1' || $datos['permite_prestamo'] === 1 || $datos['permite_prestamo'] === true) {
                    $intPermitePrestamo = 1;
                } else {
                    $intPermitePrestamo = 0;
                }

                $result = $this->model->insertEstado($strNombre, $strDescripcion, $intPermitePrestamo);

                if($result) {
                    $response = [
                        "status" => true,
                        "message" => "Estado agregado exitosamente",
                        "data" => $result,
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al agregar estado",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al agregar estado, solo se permiten métodos POST",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function agregarEstado

    public function eliminarEstado($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'DELETE') {
                $id = null;
                if (!empty($params)) {
                    $id = intval($params);
                }

                if ($id && $id > 0) {
                    // Verificar que el estado existe antes de eliminar
                    $estadoExists = $this->model->getEstadoById($id);
                    
                    if (empty($estadoExists)) {
                        $response = [
                            "status" => false,
                            "message" => "El estado con ID {$id} no existe",
                        ];
                        $code = 404;
                    } else {
                        // Call the model to delete the estado
                        $deleted = $this->model->deleteEstado($id);

                        if ($deleted) {
                            $response = [
                                "status" => true,
                                "message" => "Estado eliminado correctamente",
                                "id" => $id
                            ];
                            $code = 200;
                        } else {
                            $response = [
                                "status" => false,
                                "message" => "Error al eliminar el estado",
                            ];
                            $code = 500;
                        }
                    }
                } else {
                    $response = [
                        "status" => false,
                        "message" => "ID no válido. Debe proporcionar un ID positivo en la URL",
                    ];
                    $code = 400;
                }
            } else {
                $response = [
                    "status" => false,
                    "message" => "Método no permitido, solo se permite DELETE",
                ];
                $code = 405;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            $response = [
                "status" => false,
                "message" => "Error interno del servidor: " . $e->getMessage(),
            ];
            jsonResponse($response, 500);
        }
    }

    public function actualizarEstado($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'PUT') {

                // Validar que se proporcione el ID en la URL
                if (empty($params) || !is_numeric($params)) {
                    $response = [
                        "status" => false,
                        "message" => "El ID del estado es requerido en la URL y debe ser numérico",
                    ];
                    jsonResponse($response, 400);
                    die();
                }

                // Verificar que el estado existe antes de actualizar
                $estadoExists = $this->model->getEstadoById(intval($params));
                
                if (empty($estadoExists)) {
                    $response = [
                        "status" => false,
                        "message" => "El estado con ID {$params} no existe",
                    ];
                    jsonResponse($response, 404);
                    die();
                }

                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_estado']) || !testString($datos['nombre_estado'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre del estado es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['descripcion']) || !testString($datos['descripcion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La descripción es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(!isset($datos['permite_prestamo'])) {
                    $response = [
                        "status" => false,
                        "message" => "El campo permite_prestamo es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                // Validar que permite_prestamo sea válido (0 o 1, true o false)
                $valoresValidos = ['0', '1', 0, 1, true, false];
                if(!in_array($datos['permite_prestamo'], $valoresValidos, true)) {
                    $response = [
                        "status" => false,
                        "message" => "El campo permite_prestamo debe ser 0, 1, true o false",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $idEstado = intval($params);
                $strNombre = ucwords(strClean($datos['nombre_estado']));
                $strDescripcion = strClean($datos['descripcion']);
                
                // Convertir permite_prestamo a entero (true/1 = 1, false/0 = 0)
                if($datos['permite_prestamo'] === '1' || $datos['permite_prestamo'] === 1 || $datos['permite_prestamo'] === true) {
                    $intPermitePrestamo = 1;
                } else {
                    $intPermitePrestamo = 0;
                }

                $result = $this->model->updateEstado($idEstado, $strNombre, $strDescripcion, $intPermitePrestamo);

                if ($result) {
                    $response = [
                        "status" => true,
                        "message" => "Estado actualizado exitosamente",
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al actualizar estado",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al actualizar estado, solo se permiten métodos PUT",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function actualizarEstado

    // ========== CRUD PARA ESTADO_PRESTAMO ==========

    public function fetchEstadosPrestamo()
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'GET') {
                $datos = $this->model->getEstadosPrestamo();

                if (empty($datos)) {
                    $response = [
                        "status" => false,
                        "message" => "No hay registros en la base de datos",
                    ];
                    $code = 200;
                    jsonResponse($response, $code);
                    die();
                } else {
                    $response = array(
                        "status" => true,
                        "message" => "Datos encontrados",
                        "data" => $datos,
                    );
                    $code = 200;
                }
            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al consultar solo se permiten metodos GET",
                ];
                $code = 200;
                jsonResponse($response, $code);
            }

            $code = 200;
            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function agregarEstadoPrestamo()
    {
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if($method == 'POST') {
                
                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_estado']) || !testString($datos['nombre_estado'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre del estado de préstamo es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['descripcion']) || !testString($datos['descripcion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La descripción es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $strNombre = ucwords(strClean($datos['nombre_estado']));
                $strDescripcion = strClean($datos['descripcion']);

                $result = $this->model->insertEstadoPrestamo($strNombre, $strDescripcion);

                if($result) {
                    $response = [
                        "status" => true,
                        "message" => "Estado de préstamo agregado exitosamente",
                        "data" => $result,
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al agregar estado de préstamo",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al agregar estado de préstamo, solo se permiten métodos POST",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function agregarEstadoPrestamo

    public function eliminarEstadoPrestamo($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'DELETE') {
                $id = null;
                if (!empty($params)) {
                    $id = intval($params);
                }

                if ($id && $id > 0) {
                    // Verificar que el estado de préstamo existe antes de eliminar
                    $estadoPrestamoExists = $this->model->getEstadoPrestamoById($id);
                    
                    if (empty($estadoPrestamoExists)) {
                        $response = [
                            "status" => false,
                            "message" => "El estado de préstamo con ID {$id} no existe",
                        ];
                        $code = 404;
                    } else {
                        // Call the model to delete the estado_prestamo
                        $deleted = $this->model->deleteEstadoPrestamo($id);

                        if ($deleted) {
                            $response = [
                                "status" => true,
                                "message" => "Estado de préstamo eliminado correctamente",
                                "id" => $id
                            ];
                            $code = 200;
                        } else {
                            $response = [
                                "status" => false,
                                "message" => "Error al eliminar el estado de préstamo",
                            ];
                            $code = 500;
                        }
                    }
                } else {
                    $response = [
                        "status" => false,
                        "message" => "ID no válido. Debe proporcionar un ID positivo en la URL",
                    ];
                    $code = 400;
                }
            } else {
                $response = [
                    "status" => false,
                    "message" => "Método no permitido, solo se permite DELETE",
                ];
                $code = 405;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            $response = [
                "status" => false,
                "message" => "Error interno del servidor: " . $e->getMessage(),
            ];
            jsonResponse($response, 500);
        }
    }

    public function actualizarEstadoPrestamo($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'PUT') {

                // Validar que se proporcione el ID en la URL
                if (empty($params) || !is_numeric($params)) {
                    $response = [
                        "status" => false,
                        "message" => "El ID del estado de préstamo es requerido en la URL y debe ser numérico",
                    ];
                    jsonResponse($response, 400);
                    die();
                }

                // Verificar que el estado de préstamo existe antes de actualizar
                $estadoPrestamoExists = $this->model->getEstadoPrestamoById(intval($params));
                
                if (empty($estadoPrestamoExists)) {
                    $response = [
                        "status" => false,
                        "message" => "El estado de préstamo con ID {$params} no existe",
                    ];
                    jsonResponse($response, 404);
                    die();
                }

                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_estado']) || !testString($datos['nombre_estado'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre del estado de préstamo es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['descripcion']) || !testString($datos['descripcion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La descripción es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $idEstadoPrestamo = intval($params);
                $strNombre = ucwords(strClean($datos['nombre_estado']));
                $strDescripcion = strClean($datos['descripcion']);

                $result = $this->model->updateEstadoPrestamo($idEstadoPrestamo, $strNombre, $strDescripcion);

                if ($result) {
                    $response = [
                        "status" => true,
                        "message" => "Estado de préstamo actualizado exitosamente",
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al actualizar estado de préstamo",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al actualizar estado de préstamo, solo se permiten métodos PUT",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function actualizarEstadoPrestamo

    } // End class Estados


?>