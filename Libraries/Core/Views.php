<?php

    class Views{
        function getView($controller,$view,$data="")
		{
            $controller = get_class($controller);
            if($controller == "Home"){
				$view = "Views/".$view.".php"; // Views/home.php
			}else{
				$view = "Views/".$controller."/".$view.".php"; // Views/Cliente/cliente.php
			}
            require_once ($view);
        }
    }

?>