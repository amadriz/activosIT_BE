<?php
    class Home extends Controllers{

        public function __construct()
        {
            parent::__construct();
        }

        public function home($params)
        {
            $data['page_tag'] = "Inicio";
            $data['page_title'] = "Sistema de gestión de activos IT para instituciones educativas";
            $data['page_name'] = "home";
            $this->views->getView($this,"home",$data);
        }


    }


?>