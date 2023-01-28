<?php 

class Authentication extends Controller{
    public function decrypt($value=''){
        $model = $this->model("authentication");
        echo $model->decryptValue($value);
    }
    public function signup(){
        $model = $this->model('authentication');
        $isSignedUp = $model->signUp($_POST);
        $result = [];
        if ($isSignedUp == 1){
            $result = [
                "status" => "success",
                "message" => "user has signed up in database."
            ];
        }
        else if ($isSignedUp == -1){
            $result = [
                "status" => "error",
                "message" => "user is in the database"
            ];
        }
        else{
            $result = [
                "status" => "error",
                "message" => "validation failed or some fields haven't provided."
            ];
        }
        $this->view("json", $result);
    }

    public function login(){
        $model = $this->model('authentication');
        $validateRequiredFields = Validator::validateElements($_POST, [
            'username',
            'password'
        ]);
        if ($validateRequiredFields){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $isSuccess = $model->login($username, $password);
            $result = [];
            if ($isSuccess == true){
                $result = [
                    "status" => "success",
                    "message" => "login successfully"
                ];
            }else{
                $result = [
                    "status" => "error",
                    "message" => "login failed"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "you haven't provide specified fields"
            ];
        }
        $this->view("json", $result);
    }
}


?>
