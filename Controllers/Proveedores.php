<?php
    class Proveedores extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function fetchProveedores()
    {
        try
        {

            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'GET') {

                $datos = $this->model->getProveedores();

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

    public function agregarProveedor()
    {
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if($method == 'POST') {
                
                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_proveedor']) || !testAlphanumeric($datos['nombre_proveedor'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre del proveedor es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['contacto']) || !testString($datos['contacto'])) {
                    $response = [
                        "status" => false,
                        "message" => "El contacto es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['telefono']) || !preg_match('/^[\d\-\s\+\(\)]+$/', $datos['telefono'])) {
                    $response = [
                        "status" => false,
                        "message" => "El teléfono es requerido y debe tener formato válido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                    $response = [
                        "status" => false,
                        "message" => "El email es requerido y debe tener formato válido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['direccion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La dirección es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $strNombreProveedor = ucwords(strClean($datos['nombre_proveedor']));
                $strContacto = ucwords(strClean($datos['contacto']));
                $strTelefono = strClean($datos['telefono']);
                $strEmail = strtolower(strClean($datos['email']));
                $strDireccion = strClean($datos['direccion']);

                $result = $this->model->insertProveedor($strNombreProveedor, $strContacto, $strTelefono, $strEmail, $strDireccion);

                if($result) {
                    $response = [
                        "status" => true,
                        "message" => "Proveedor agregado exitosamente",
                        "data" => $result,
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al agregar proveedor",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al agregar proveedor, solo se permiten métodos POST",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function agregarProveedor

    public function eliminarProveedor($params = null)
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
                    // Verificar que el proveedor existe antes de eliminar
                    $proveedorExists = $this->model->getProveedorById($id);
                    
                    if (empty($proveedorExists)) {
                        $response = [
                            "status" => false,
                            "message" => "El proveedor con ID {$id} no existe",
                        ];
                        $code = 404;
                    } else {
                        // Call the model to delete the proveedor
                        $deleted = $this->model->deleteProveedor($id);

                        if ($deleted) {
                            $response = [
                                "status" => true,
                                "message" => "Proveedor eliminado correctamente",
                                "id" => $id
                            ];
                            $code = 200;
                        } else {
                            $response = [
                                "status" => false,
                                "message" => "Error al eliminar el proveedor",
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

    public function actualizarProveedor($params = null)
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'PUT') {

                // Validar que se proporcione el ID en la URL
                if (empty($params) || !is_numeric($params)) {
                    $response = [
                        "status" => false,
                        "message" => "El ID del proveedor es requerido en la URL y debe ser numérico",
                    ];
                    jsonResponse($response, 400);
                    die();
                }

                // Verificar que el proveedor existe antes de actualizar
                $proveedorExists = $this->model->getProveedorById(intval($params));
                
                if (empty($proveedorExists)) {
                    $response = [
                        "status" => false,
                        "message" => "El proveedor con ID {$params} no existe",
                    ];
                    jsonResponse($response, 404);
                    die();
                }

                $json = file_get_contents('php://input');
                $datos = json_decode($json, true);

                if(empty($datos['nombre_proveedor']) || !testAlphanumeric($datos['nombre_proveedor'])) {
                    $response = [
                        "status" => false,
                        "message" => "El nombre del proveedor es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['contacto']) || !testAlphanumeric($datos['contacto'])) {
                    $response = [
                        "status" => false,
                        "message" => "El contacto es requerido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['telefono']) || !preg_match('/^[\d\-\s\+\(\)]+$/', $datos['telefono'])) {
                    $response = [
                        "status" => false,
                        "message" => "El teléfono es requerido y debe tener formato válido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                    $response = [
                        "status" => false,
                        "message" => "El email es requerido y debe tener formato válido",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                if(empty($datos['direccion'])) {
                    $response = [
                        "status" => false,
                        "message" => "La dirección es requerida",
                    ];
                    jsonResponse($response, 200);
                    die();
                }

                $idProveedor = intval($params);
                $strNombreProveedor = ucwords(strClean($datos['nombre_proveedor']));
                $strContacto = ucwords(strClean($datos['contacto']));
                $strTelefono = strClean($datos['telefono']);
                $strEmail = strtolower(strClean($datos['email']));
                $strDireccion = strClean($datos['direccion']);

                $result = $this->model->updateProveedor($idProveedor, $strNombreProveedor, $strContacto, $strTelefono, $strEmail, $strDireccion);

                if ($result) {
                    $response = [
                        "status" => true,
                        "message" => "Proveedor actualizado exitosamente",
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al actualizar proveedor",
                    ];
                }

                $code = 200;

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error al actualizar proveedor, solo se permiten métodos PUT",
                ];
                $code = 200;
            }

            jsonResponse($response, $code);
            die();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } // End function actualizarProveedor

    } // End class Proveedores


?>