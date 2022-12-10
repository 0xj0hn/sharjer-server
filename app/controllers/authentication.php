<?php 

class Authentication extends Controller{
    private $data;
    public function __construct(){
        $this->data = $_POST;
    }

    public function signup(){
        $model = $this->model('authentication');
        $isSignedUp = $model->signUp($this->data);

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
        $validateRequiredFields = Validator::validateElements($this->data, [
            'username',
            'password'
        ]);
        if ($validateRequiredFields){
            $isSuccess = $model->login($this->data['username'], $this->data['password']);
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
