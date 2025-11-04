<?php
    class Ubicaciones extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function fetchUbicaciones()
    {
        try
        {

            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'GET') {

                $datos = $this->model->getUbicaciones();

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

    public function agregarUbicacion()
    {
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if($method == 'POST') {
                
                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['edificio']) || !testAlphanumeric($datos['edificio'])) {
                    $response = [
                        "status" => false,
                        "message" => "El edificio es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['piso']) || !is_numeric($datos['piso'])) {
                    $response = [
                        "status" => false,
                        "message" => "El piso es requerido y debe ser numérico",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['aula_oficina']) || !testAlphanumeric($datos['aula_oficina'])) {
                    $response = [
                        "status" => false,
                        "message" => "El aula/oficina es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['descripcion']) || !testAlphanumeric($datos['descripcion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La descripción es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $strEdificio = ucwords(strClean($datos['edificio']));
                $intPiso = intval($datos['piso']);
                $strAulaOficina = ucwords(strClean($datos['aula_oficina']));
                $strDescripcion = strClean($datos['descripcion']);

                $result = $this->model->insertUbicacion($strEdificio, $intPiso, $strAulaOficina, $strDescripcion);

                if($result) {
                    $response = [
                        "status" => true,
                        "message" => "Ubicación agregada exitosamente",
                        "data" => $result,
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al agregar ubicación",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al agregar ubicación, solo se permiten métodos POST",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function agregarUbicacion

    public function eliminarUbicacion($params = null)
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
                    // Verificar que la ubicación existe antes de eliminar
                    $ubicacionExists = $this->model->getUbicacionById($id);
                    
                    if (empty($ubicacionExists)) {
                        $response = [
                            "status" => false,
                            "message" => "La ubicación con ID {$id} no existe",
                        ];
                        $code = 404;
                    } else {
                        // Call the model to delete the ubicacion
                        $deleted = $this->model->deleteUbicacion($id);

                        if ($deleted) {
                            $response = [
                                "status" => true,
                                "message" => "Ubicación eliminada correctamente",
                                "id" => $id
                            ];
                            $code = 200;
                        } else {
                            $response = [
                                "status" => false,
                                "message" => "Error al eliminar la ubicación",
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

    public function actualizarUbicacion($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'PUT') {

                // Validar que se proporcione el ID en la URL
                if (empty($params) || !is_numeric($params)) {
                    $response = [
                        "status" => false,
                        "message" => "El ID de la ubicación es requerido en la URL y debe ser numérico",
                    ];
                    jsonResponse($response, 400);
                    die();
                }

                // Verificar que la ubicación existe antes de actualizar
                $ubicacionExists = $this->model->getUbicacionById(intval($params));
                
                if (empty($ubicacionExists)) {
                    $response = [
                        "status" => false,
                        "message" => "La ubicación con ID {$params} no existe",
                    ];
                    jsonResponse($response, 404);
                    die();
                }

                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['edificio']) || !testAlphanumeric($datos['edificio'])) {
                    $response = [
                        "status" => false,
                        "message" => "El edificio es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['piso']) || !is_numeric($datos['piso'])) {
                    $response = [
                        "status" => false,
                        "message" => "El piso es requerido y debe ser numérico",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['aula_oficina']) || !testAlphanumeric($datos['aula_oficina'])) {
                    $response = [
                        "status" => false,
                        "message" => "El aula/oficina es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['descripcion']) || !testAlphanumeric($datos['descripcion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La descripción es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $idUbicacion = intval($params);
                $strEdificio = ucwords(strClean($datos['edificio']));
                $intPiso = intval($datos['piso']);
                $strAulaOficina = ucwords(strClean($datos['aula_oficina']));
                $strDescripcion = strClean($datos['descripcion']);

                $result = $this->model->updateUbicacion($idUbicacion, $strEdificio, $intPiso, $strAulaOficina, $strDescripcion);

                if ($result) {
                    $response = [
                        "status" => true,
                        "message" => "Ubicación actualizada exitosamente",
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al actualizar ubicación",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al actualizar ubicación, solo se permiten métodos PUT",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function actualizarUbicacion

    } // End class Ubicaciones


?>