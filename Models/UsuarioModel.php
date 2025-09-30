<?php

    //Herencia de la clase padre Mysql
    class UsuarioModel extends Mysql
    {

        private $intIdUsuario;
        private $strNombre;
        private $strApellido;
        private $strEmail;
        private $strPassword;
        private $strRol;

        
        public function __construct()
        {
            //Cargamos el constructor de la clase padre
            
            parent::__construct();
        }

        //fetch all
        public function getUsuarios(){
            $sql = "SELECT id_usuario,
                            nombre,
                            apellido,
                            email,
                            DATE_FORMAT(datecreated, '%d-%m-%Y') as fechaRegistro,
                            rol
                            FROM usuario WHERE status != 0";
            
            
            $request = $this->select_all($sql);
            
            
            return $request;


        }

        //Insertar un nuevo usuario
        public function insertUsuario( string $nombre, string $apellido, string $email, string $password, string $rol){

            $this->strNombre = $nombre;
            $this->strApellido = $apellido;
            $this->strEmail = $email;
            $this->strPassword = $password;
            $this->strRol = $rol;

            // dep(get_object_vars($this));

            //Sql query para validar que el usuario no exista
            $sql = "SELECT * FROM usuario WHERE email = '{$this->strEmail}' and Status != 0";
            
            $request = $this->select_all($sql);

            // dep($request);

            if(empty($request))
            {
                $query_insert = "INSERT INTO usuario(nombre, apellido, email, password, rol) 
                VALUES(:nom, :ape, :email, :pass, :rol)";

                $arrData = array(':nom' => $this->strNombre,
                                 ':ape' => $this->strApellido,
                                 ':email' => $this->strEmail,
                                 ':pass' => $this->strPassword,
                                 ':rol' => $this->strRol
                                );

                $request_insert = $this->insert($query_insert, $arrData);

                return $request_insert;
            }else{                
                return false;
            }


        } //Fin de la función insertUsuario

        public function loginUser(string $email, string $password)
        {
            $this->strEmail = $email;
            $this->strPassword = $password;

            //BINARY para que sea case sensitive
            $sql = "SELECT id_usuario, status, rol FROM usuario WHERE email =  BINARY :email AND password = BINARY :pass AND status != 0";
            $arrData = array(":email" => $this->strEmail, ":pass" => $this->strPassword);
            $request = $this->select($sql,$arrData);

            return $request;
        } //Fin de la función loginUser

    } //Fin de la clase UsuarioModel

    