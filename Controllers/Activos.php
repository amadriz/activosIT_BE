<?php
    class Activos extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

    public function fetchActivos()
    {
        try
        {

            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'GET') {

                $datos = $this->model->getActivos();

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

    public function agregarActivos()
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'POST') {
                
                // Get JSON input
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                // Validate required fields
                // $required_fields = [
                //     'nombre_activo', 'id_categoria', 'id_marca', 'id_ubicacion', 
                //     'id_estado', 'modelo', 'numero_serie', 'fecha_adquisicion', 
                //     'costo_adquisicion', 'usuario_registro'
                // ];

                // $missing_fields = [];
                // foreach ($required_fields as $field) {
                //     if (!isset($data[$field]) || empty($data[$field])) {
                //         $missing_fields[] = $field;
                //     }
                // }

                // if (!empty($missing_fields)) {
                //     $response = [
                //         "status" => false,
                //         "message" => "Faltan campos requeridos: " . implode(', ', $missing_fields),
                //     ];
                //     $code = 400;
                //     jsonResponse($response, $code);
                //     die();
                // }

                // // Validate numeric fields
                // if (!is_numeric($data['id_categoria']) || !is_numeric($data['id_marca']) || 
                //     !is_numeric($data['id_ubicacion']) || !is_numeric($data['id_estado']) ||
                //     !is_numeric($data['costo_adquisicion'])) {
                //     $response = [
                //         "status" => false,
                //         "message" => "Los campos de ID y costo deben ser numéricos",
                //     ];
                //     $code = 400;
                //     jsonResponse($response, $code);
                //     die();
                // }

                // // Validate date format for fecha_adquisicion
                // if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_adquisicion'])) {
                //     $response = [
                //         "status" => false,
                //         "message" => "El formato de fecha_adquisicion debe ser YYYY-MM-DD",
                //     ];
                //     $code = 400;
                //     jsonResponse($response, $code);
                //     die();
                // }

                $fecha_garantia_inicio = isset($data['fecha_garantia_inicio']) ? $data['fecha_garantia_inicio'] : null;
                $fecha_garantia_fin = isset($data['fecha_garantia_fin']) ? $data['fecha_garantia_fin'] : null;
                $especificaciones_tecnicas = isset($data['especificaciones_tecnicas']) ? $data['especificaciones_tecnicas'] : null;
                $observaciones = isset($data['observaciones']) ? $data['observaciones'] : null;

                // // Validate optional date fields if provided
                // if ($fecha_garantia_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_garantia_inicio)) {
                //     $response = [
                //         "status" => false,
                //         "message" => "El formato de fecha_garantia_inicio debe ser YYYY-MM-DD",
                //     ];
                //     $code = 400;
                //     jsonResponse($response, $code);
                //     die();
                // }

                // if ($fecha_garantia_fin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_garantia_fin)) {
                //     $response = [
                //         "status" => false,
                //         "message" => "El formato de fecha_garantia_fin debe ser YYYY-MM-DD",
                //     ];
                //     $code = 400;
                //     jsonResponse($response, $code);
                //     die();
                // }

                // Call model method to insert activo
                $result = $this->model->insertActivos(
                    $data['nombre_activo'],
                    $data['id_categoria'],
                    $data['id_marca'],
                    $data['id_ubicacion'],
                    $data['id_estado'],
                    $data['modelo'],
                    $data['numero_serie'],
                    $data['fecha_adquisicion'],
                    $data['costo_adquisicion'],
                    $fecha_garantia_inicio,
                    $fecha_garantia_fin,
                    $especificaciones_tecnicas,
                    $observaciones,
                    $data['usuario_registro']
                );

                if ($result > 0) {
                    $response = [
                        "status" => true,
                        "message" => "Activo insertado correctamente",
                        "id_activo" => $result
                    ];
                    $code = 201;
                } else {
                    $response = [
                        "status" => false,
                        "message" => "Error al insertar el activo",
                    ];
                    $code = 500;
                }

            } else {
                $response = [
                    "status" => false,
                    "message" => "Error: solo se permiten métodos POST",
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
            $code = 500;
            jsonResponse($response, $code);
            die();
        }
    }

    public function eliminarActivos($params = null){
        // Delete agent by ID from URL parameter
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];

            if ($method == 'DELETE') {
                $id = null;
                if (!empty($params)) {
                    $id = intval($params);
                }

                if ($id && $id > 0) {
                    // Call the model to delete the agent
                    $deleted = $this->model->deleteActivo($id);

                    if ($deleted) {
                        $response = [
                            "status" => true,
                            "message" => "Activo eliminado correctamente",
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
    

}


?>