<?php 
include "./app/core/bootstrap.php";
include "./app/core/jdf/jdf.php";
include "./app/core/App.php";
include "./app/core/Validator.php";
include "./app/core/Controller.php";
include "./app/core/DB.php";
include "./app/core/Model.php";
include "./app/models/data_models.php";

ini_set("display_errors", 1);
define("OK", 1);
define("ERROR", 0);
define("DOES_NOT_EXIST", -1);

$app = new App;


?>
