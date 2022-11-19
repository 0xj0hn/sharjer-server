<?php

class App{
    protected $controller = "home";
    protected $method = "index";
    protected $params = [];

    public function __construct(){
        $url = $this->parse_url(); //get url splitted by «/»
        $file = "app/controllers/" . $this->controller . ".php";
        if ($url[0]){
            $this->controller = $url[0];
            $file = "app/controllers/" . $this->controller . ".php";
            if (!file_exists($file)) return; //if there is no file there, then exit from the function.
            unset($url[0]); 
        }
        //INCLUDE CONTROLLER.
        include $file;


        //METHOD CHECKER
        if (isset($url[1])){
            $this->method = $url[1];
            unset($url[1]);
            if (method_exists($this->controller, $this->method)){
                $this->controller = new $this->controller;
                $this->params = $url ? array_values($url) : []; //rebase indexes.
                call_user_func_array([$this->controller, $this->method], $this->params);
            }
        }

    }

    public function parse_url(){
        if (isset($_GET["url"])){
            $url = $_GET["url"];
            $trimmed_url = rtrim($url, '/');
            $splitted_url = explode('/', $trimmed_url);
            return $splitted_url;
        }
    }
}

?>
