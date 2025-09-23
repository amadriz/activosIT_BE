<?php

//Esto es para no utilizar el require_once en cada clase que se cree


    spl_autoload_register(function($class){
        if(file_exists("Libraries/Core/".$class.".php")){
            require_once("Libraries/Core/".$class.".php");
        }
    });
?>