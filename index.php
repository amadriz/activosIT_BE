<?php
    require_once("Config/Config.php");
    require_once("Helpers/Helpers.php");

    // CORS headers - Allow your frontend to access the API
    // Determine the allowed origin based on environment
    $allowedOrigins = [
        'http://localhost:5173',  // Development
        //'https://pecosacr.com',   // Production
        //'http://pecosacr.com'     // Production without SSL (fallback)
    ];
    
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: " . $origin);
    } else {
        // Default to production domain if origin is not recognized
        header("Access-Control-Allow-Origin: https://pecosacr.com");
    }
    
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: X-API-KEY, Access-Control-Request-Method, Content-Type, X-Auth-Token, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");


    //funcion para cargar las vistas, controladores y modelos automaticamente
    $url = !empty($_GET['url']) ? $_GET['url'] : "home/home" ;
    $arrUrl = explode("/",$url);
    $controller = $arrUrl[0];
    $method =  $arrUrl[0];
    $params = "";

    if(!empty($arrUrl[1])){
        if($arrUrl[1] != ""){
            $method = $arrUrl[1]; 
        }
    }

    if(!empty($arrUrl[2]) && $arrUrl[2] != "")
    {
        for ($i=2; $i < count($arrUrl); $i++) { 
            $params .= $arrUrl[$i].',';
        }
        $params = trim($params,",");
    }

    require_once("Libraries/Core/Autoload.php");
    require_once("Libraries/Core/Load.php");

?>