<?php
    class Categorias extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function fetchCategorias()
        {
            try
            {

                $method = $_SERVER['REQUEST_METHOD'];
                $response = [];

                if ($method == 'GET') {

                    $datos = $this->model->getCategorias();

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

        public function agregarCategoria()
        {
            try{
                $method = $_SERVER['REQUEST_METHOD'];
                $response = [];

                if($method == 'POST') {
                    
                    $json = file_get_contents('php://input');
                    $datos = json_decode($json, true);

                    if(empty($datos['nombre_categoria']) || !testString($datos['nombre_categoria'])) {
                        $response = [
                            "status" => false,
                            "message" => "El nombre de la categoría es requerido",
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

                    if(!isset($datos['estado']) || !testString($datos['estado'])) {
                        $response = [
                            "status" => false,
                            "message" => "El estado es requerido",
                        ];
                        jsonResponse($response, 200);
                        die();
                    }

                    $strNombre = ucwords(strClean($datos['nombre_categoria']));
                    $strDescripcion = strClean($datos['descripcion']);
                    $intEstado = intval($datos['estado']);

                    $result = $this->model->insertCategoria($strNombre, $strDescripcion, $intEstado);

                    // show data from result for debugging
                    if($result) {
                        $response = [
                            "status" => true,
                            "message" => "Categoría agregada exitosamente",
                            "data" => $result,
                        ];
                    } else {
                        $response = [
                            "status" => false,
                            "message" => "Error al agregar categoría",
                        ];
                    }

                    $code = 200;

                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al agregar categoría, solo se permiten métodos POST",
                    ];
                    $code = 200;
                }

                jsonResponse($response, $code);
                die();

            } catch (Exception $e) {
                echo $e->getMessage();
            }


        } // End function agregarCategoria

        public function eliminarCategoria($params = null)
        {
            // Delete category by ID from URL parameter
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                $response = [];

                if ($method == 'DELETE') {
                    $id = null;
                    if (!empty($params)) {
                        $id = intval($params);
                    }

                if ($id && $id > 0) {
                    // Call the model to delete the category
                    $deleted = $this->model->deleteCategoria($id);

                    if ($deleted) {
                        $response = [
                            "status" => true,
                            "message" => "Categoría eliminada correctamente",
                            "id" => $id
                        ];
                        $code = 200;
                    } else {
                        $response = [
                            "status" => false,
                            "message" => "Error al eliminar el Activo o el registro no existe",
                        ];
                        $code = 404;
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

        // add method to update category
        public function actualizarCategoria($params = null)
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                $response = [];

                if ($method == 'PUT') {
                    // Get category ID from URL parameter
                    $id = null;
                    if (!empty($params)) {
                        $id = intval($params);
                    }

                    if (!$id || $id <= 0) {
                        $response = [
                            "status" => false,
                            "message" => "ID no válido. Debe proporcionar un ID positivo en la URL",
                        ];
                        jsonResponse($response, 400);
                        die();
                    }

                    $json = file_get_contents('php://input');
                    $datos = json_decode($json, true);

                    // Validate JSON data
                    if (empty($datos)) {
                        $response = [
                            "status" => false,
                            "message" => "No se recibieron datos válidos en el cuerpo de la solicitud",
                        ];
                        jsonResponse($response, 400);
                        die();
                    }

                    // Validate required fields
                    if (empty($datos['nombre_categoria'])) {
                        $response = [
                            "status" => false,
                            "message" => "El nombre de la categoría es requerido",
                        ];
                        jsonResponse($response, 400);
                        die();
                    }

                    if (empty($datos['descripcion']) ) {
                        $response = [
                            "status" => false,
                            "message" => "La descripción es requerida",
                        ];
                        jsonResponse($response, 400);
                        die();
                    }

                    if (!isset($datos['estado'])) {
                        $response = [
                            "status" => false,
                            "message" => "El estado es requerido",
                        ];
                        jsonResponse($response, 400);
                        die();
                    }

                    $strNombre = ucwords(strClean($datos['nombre_categoria']));
                    $strDescripcion = strClean($datos['descripcion']);
                    $strEstado = strClean($datos['estado']);

                    $result = $this->model->updateCategoria($id, $strNombre, $strDescripcion, $strEstado);

                    if ($result) {
                        $response = [
                            "status" => true,
                            "message" => "Categoría actualizada exitosamente",
                            "data" => $result,
                        ];
                    } else {
                        $response = [
                            "status" => false,
                            "message" => "Error al actualizar categoría o no se realizaron cambios",
                        ];
                    }

                    $code = 200;

                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al actualizar categoría, solo se permiten métodos PUT",
                    ];
                    $code = 200;
                }

                jsonResponse($response, $code);
                die();

            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

    }// End class Categorias


?>