<?php
    require_once("Config/Config.php");
    require_once("Helpers/Helpers.php");

    // CORS headers - Allow your frontend to access the API
    // Determine the allowed origins based on environment
    $allowedOrigins = [
        'http://localhost:5173',  // Development
        'https://supersaloncr.com',   // Production domain
        'http://supersaloncr.com',    // Production without SSL (fallback)
        'https://www.supersaloncr.com',  // www variant
        'http://www.supersaloncr.com',   // www without SSL
        'https://supersaloncr.com/activosituhispa',  // Subdirectory
        'http://supersaloncr.com/activosituhispa'    // Subdirectory without SSL
    ];
    
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: " . $origin);
    } else {
        // For development/debugging - be more permissive with supersaloncr.com subdomains
        if (strpos($origin, 'supersaloncr.com') !== false) {
            header("Access-Control-Allow-Origin: " . $origin);
        } else {
            // Default to production domain if origin is not recognized
            header("Access-Control-Allow-Origin: https://supersaloncr.com");
        }
    }
    
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: X-API-KEY, Access-Control-Request-Method, Content-Type, X-Auth-Token, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Max-Age: 86400"); // Cache preflight for 24 hours
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        // Return 200 OK for preflight requests
        http_response_code(200);
        exit();
    }

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