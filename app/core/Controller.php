<?php 

class Controller{
    protected function model($model){
        include "app/models/" . $model . ".php";
        $model = $model . "model"; //make them together. (class of Model)
        return new $model; //return object.
    }
}


?>
