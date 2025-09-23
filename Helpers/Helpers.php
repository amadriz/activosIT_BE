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
    

?>