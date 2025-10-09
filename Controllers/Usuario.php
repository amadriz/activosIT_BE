<?php
    class Usuario extends Controllers{

        public $views;

        public function __construct()
        {
            parent::__construct();
            
        }


        public function fetchUsers()
        {
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                $response = [];
                if($method == "GET")
                {
                    $arrData = $this->model->getUsuarios();
                    if(empty($arrData))
                    {
                        $response = array('status' => false , 'msg' => 'No hay datos para mostrar', 'data' => '');
                    }else{
                        $response = array('status' => true , 'msg' => 'Datos encontrados ', 'data' =>  $arrData);
                    }
                    $code = 200;
                }else{
                    $response = array('status' => false , 'msg' => 'Error en la solicitud '.$method);
                    $code = 400;
                }
                jsonResponse($response,$code);
                die();

            } catch (Exception $ex) {
                // Handle the exception if the token is invalid or decoding fails
                $arrResponse = array('status' => false, 'message' => 'Token no es válido => '.$ex->getMessage());
                jsonResponse($arrResponse, 401);
                die();
            }
            die();
        }

        public function registro(){
            try{

                $method = $_SERVER["REQUEST_METHOD"];
                $response = [];

                if($method == "POST"){
                    

                    $_POST = json_decode(file_get_contents('php://input'), true);
    
                        //Validar nombre
                        if(empty($_POST['nombre']) || !testString($_POST['nombre'])){
                                
                        
                                $response = array(
                                "status" => false,
                                "message" => "El nombre es requerido"
                                );
                                jsonResponse($response, 200);
                                die();
        
                        }

                        //Validar apellido
                        if(empty($_POST['apellido']) || !testString($_POST['apellido'])){
                                
                        
                            $response = array(
                            "status" => false,
                            "message" => "El apellido es requerido"
                            );
                            jsonResponse($response, 200);
                            die();

                        }

                        //Validar email
                        if(empty($_POST['email']) || !testEmail($_POST['email'])){
                                
                        
                            $response = array(
                            "status" => false,
                            "message" => "El email es requerido"
                            );
                            jsonResponse($response, 200);
                            die();

                        }

                        //Validar password
                        if(empty($_POST['password'])){
                            $response = array(
                            "status" => false,
                            "message" => "El password es requerido"
                            );
                            jsonResponse($response, 200);
                            die();
                        }
                        //Validar rol
                        if(empty($_POST['rol']) || !testString($_POST['rol'])){
                            $response = array(
                            "status" => false,
                            "message" => "El rol es requerido"
                            );
                            jsonResponse($response, 200);
                            die();
                        }

                        $strNombre = ucwords(strClean($_POST['nombre']));
                        $strApellido = ucwords(strClean($_POST['apellido']));
                        $strEmail = strtolower(strClean($_POST['email']));
                        $strPassword = hash("SHA256", $_POST['password']);
                        $strRol = strtolower(strClean($_POST['rol']));

                        $request = $this->model->insertUsuario($strNombre, 
                                                            $strApellido, 
                                                            $strEmail, 
                                                            $strPassword,
                                                            $strRol);

                        if($request > 0){
                            $response = [
                                "status" => true,
                                "msg" => "Registro de usuario exitoso"
                            ];

                            $code = 200;

                            jsonResponse($response, $code);

                        }else{
                            $response = [
                                "status" => false,
                                "msg" => "El email ya existe"
                            ];

                            $code = 400;

                            jsonResponse($response, $code);

                        }
                    


                }else{
                    $response = [
                        "status" => false,
                        "msg" => "Error en el método, debe de ser POST"
                    ];

                    $code = 400;

                    jsonResponse($response, $code);

                }
            }catch(Exception $ex){
                // Handle the exception if the token is invalid or decoding fails
                $arrResponse = array('status' => false, 'message' => 'Token no es válido => '.$ex->getMessage());
                jsonResponse($arrResponse, 401);
                die();
            }
        }//Cierre m◙todo registro

        public function login(){
            try{

                $method = $_SERVER['REQUEST_METHOD'];
                $response = [];

                if($method == "POST")
                {
                    $_POST = json_decode(file_get_contents('php://input'), true);
                    
                    if(empty($_POST['email']) || empty($_POST['password'])){
                        $response = [
                            "status" => false,
                            "msg" => "El email y el password son requeridos"
                        ];

                        $code = 200;

                        jsonResponse($response, $code);

                        die();
                    }

                    $strEmail = strClean($_POST['email']);
                    $strPassword = hash("SHA256", $_POST['password']);
                    $requestUser = $this->model->loginUser($strEmail, $strPassword);                    
                    if(empty($requestUser)){
                        $response = [
                            "status" => false,
                            "msg" => "El email o el password son incorrectos"
                        ];

                        $code = 200;

                        jsonResponse($response, $code);

                        die();
                    }else{

                        $tokenData = generateToken($requestUser['id_usuario'], $strEmail, $requestUser['rol']);
                        
                        $response = [
                            'status' => true,
                            'msg' => 'Bienvenido al sistema',
                            'auth' => $tokenData
                        ];
                        $code = 200;

                        
                        
                    }

                    jsonResponse($response, $code);
                    die();

                }else{
                    
                    $response = [
                        "status" => false,
                        "msg" => "Error en el método, debe de ser POST"
                    ];

                    $code = 400;

                    jsonResponse($response, $code);

                
                }

                $code = 200;

                jsonResponse($response, $code);
                die();

            }catch(Exception $e){
                echo "Error en el proceso login: ". $e->getMessage();
            }
            die();

        }//Cierre método login

    }//Fin de la clase Usuario