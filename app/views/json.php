<?php
header("Content-Type: application/json");
ini_set("display_errors", 0);
if ($data[0]["status"] == "error"){
    http_response_code(400);
}
echo json_encode($data[0]);
?>
