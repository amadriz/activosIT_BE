<?php

    //Retorla la url del proyecto
	function base_url()
	{
		return BASE_URL;
	}

    function media()
    {
        return BASE_URL."Assets";
    }

    //Muestra información formateada
	function dep($data)
    {
        $format  = print_r('<pre>');
        $format .= print_r($data);
        $format .= print_r('</pre>');
        return $format;
    }

    //Elimina exceso de espacios entre palabras
    //Tdodo esto para evitar inyecciones sql
    function strClean($strCadena){
        $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $strCadena);
        $string = trim($string); //Elimina espacios en blanco al inicio y al final
        $string = stripslashes($string); // Elimina las \ invertidas
        $string = str_ireplace("<script>","",$string);
        $string = str_ireplace("</script>","",$string);
        $string = str_ireplace("<script src>","",$string);
        $string = str_ireplace("<script type=>","",$string);
        $string = str_ireplace("SELECT * FROM","",$string);
        $string = str_ireplace("DELETE FROM","",$string);
        $string = str_ireplace("INSERT INTO","",$string);
        $string = str_ireplace("SELECT COUNT(*) FROM","",$string);
        $string = str_ireplace("DROP TABLE","",$string);
        $string = str_ireplace("OR '1'='1","",$string);
        $string = str_ireplace('OR "1"="1"',"",$string);
        $string = str_ireplace('OR ´1´=´1´',"",$string);
        $string = str_ireplace("is NULL; --","",$string);
        $string = str_ireplace("is NULL; --","",$string);
        $string = str_ireplace("LIKE '","",$string);
        $string = str_ireplace('LIKE "',"",$string);
        $string = str_ireplace("LIKE ´","",$string);
        $string = str_ireplace("OR 'a'='a","",$string);
        $string = str_ireplace('OR "a"="a',"",$string);
        $string = str_ireplace("OR ´a´=´a","",$string);
        $string = str_ireplace("OR ´a´=´a","",$string);
        $string = str_ireplace("--","",$string);
        $string = str_ireplace("^","",$string);
        $string = str_ireplace("[","",$string);
        $string = str_ireplace("]","",$string);
        $string = str_ireplace("==","",$string);
        return $string;
    }

    //public function response
    function jsonResponse(array $arrData, int $code){

        if(is_array($arrData))
        {            
            header("HTTP/1.1 ".$code);
            header("Content-Type: application/json");
            echo json_encode($arrData, true);
            exit;
        }
    }

    //Create function regular expression
    function testString(string $nombre)
    {
        $patron = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]*$/";
        
        if(preg_match($patron, $nombre))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //Function to validate alphanumeric strings (letters, numbers, spaces, accents)
    function testAlphanumeric(string $texto)
    {
        $patron = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 .-]*$/";
        
        if(preg_match($patron, $texto))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function testEntero(int $numero)
    {
        $patron = "/^[0-9]*$/";
        
        if(preg_match($patron, $numero))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function testEmail(string $email)
    {
        $patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})+$/";
        
        if(preg_match($patron, $email))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
  function fntAuthorization(array $arrHeaders)
    {

         if(empty($arrHeaders['Authorization']))
         {
            $response = array('status' => false, 'message' => 'Autenticación requerida');
            jsonResponse($response, 401);
            die();
         }else{
            $token = $arrHeaders['Authorization'];
            $arrTokenBearer = explode(" ", $token);
            
            //Si el token no es Bearer
            if($arrTokenBearer[0] != 'Bearer')
            {
                $response = array('status' => false, 'message' => 'Error de autenticación');
                jsonResponse($response, 401);
                die();
            }else{
                $token = $arrTokenBearer[1];
            
                // JWT decode token
                try {
                    $payload = JWT::decode($token, new Key(SECRET_KEY, 'HS512'));
                    // dep($payload);
                    // exit();
                } catch (Exception $e) {
                    // Handle the exception if the token is invalid or decoding fails
                    $arrResponse = array('status' => false, 'message' => 'Token no es válido => '.$e->getMessage());
                    jsonResponse($arrResponse, 401);
                    die();
                }
            }

           
         }
         
    } //End function fntAuthorization    
    
    
    //Metodos de autenticación
    function generateToken($userId, $email, $rol = null) {
        // Generate a token (this is a simple example, you might want to use a more secure method)
        $token = bin2hex(random_bytes(16));
        // Optionally, you can store the token in the database with an expiration time

        $tokenData = [
            'authToken' => $token,
            'user_id' => $userId,
            'email' => $email
        ];

        // Add role to token data if provided
        if ($rol !== null) {
            $tokenData['rol'] = $rol;
        }

        return $tokenData;
    }
?>