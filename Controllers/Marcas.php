<?php
    class Marcas extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function fetchMarcas()
    {
        try
        {

            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'GET') {

                $datos = $this->model->getMarcas();

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

    public function agregarMarca()
    {
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if($method == 'POST') {
                
                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_marca']) || !testAlphanumeric($datos['nombre_marca'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre de la marca es requerido",
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

                $strNombre = ucwords(strClean($datos['nombre_marca']));
                $strDescripcion = strClean($datos['descripcion']);

                $result = $this->model->insertMarca($strNombre, $strDescripcion);

                if($result) {
                    $response = [
                        "status" => true,
                        "message" => "Marca agregada exitosamente",
                        "data" => $result,
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al agregar marca",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al agregar marca, solo se permiten métodos POST",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function agregarMarca

    public function eliminarMarca($params = null)
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
                    // Verificar que la marca existe antes de eliminar
                    $marcaExists = $this->model->getMarcaById($id);
                    
                    if (empty($marcaExists)) {
                        $response = [
                            "status" => false,
                            "message" => "La marca con ID {$id} no existe",
                        ];
                        $code = 404;
                    } else {
                        // Call the model to delete the marca
                        $deleted = $this->model->deleteMarca($id);

                        if ($deleted) {
                            $response = [
                                "status" => true,
                                "message" => "Marca eliminada correctamente",
                                "id" => $id
                            ];
                            $code = 200;
                        } else {
                            $response = [
                                "status" => false,
                                "message" => "Error al eliminar la marca",
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

    public function actualizarMarca($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'PUT') {

                // Validar que se proporcione el ID en la URL
                if (empty($params) || !is_numeric($params)) {
                    $response = [
                        "status" => false,
                        "message" => "El ID de la marca es requerido en la URL y debe ser numérico",
                    ];
                    jsonResponse($response, 400);
                    die();
                }

                // Verificar que la marca existe antes de actualizar
                $marcaExists = $this->model->getMarcaById(intval($params));
                
                if (empty($marcaExists)) {
                    $response = [
                        "status" => false,
                        "message" => "La marca con ID {$params} no existe",
                    ];
                    jsonResponse($response, 404);
                    die();
                }

                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_marca']) || !testAlphanumeric($datos['nombre_marca'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre de la marca es requerido",
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

                $idMarca = intval($params);
                $strNombre = ucwords(strClean($datos['nombre_marca']));
                $strDescripcion = strClean($datos['descripcion']);

                $result = $this->model->updateMarca($idMarca, $strNombre, $strDescripcion);

                if ($result) {
                    $response = [
                        "status" => true,
                        "message" => "Marca actualizada exitosamente",
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al actualizar marca",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al actualizar marca, solo se permiten métodos PUT",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function actualizarMarca

    } // End class Marcas


?>