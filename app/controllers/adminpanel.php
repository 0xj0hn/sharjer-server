<?php 

class AdminPanel extends Controller{

    public function update_month(...$params){
        $model = $this->model('admin');
        $month = $params[0];
        $validateParams = Validator::validateElements($_POST, [
            'username',
            'password'
        ]);
        if($month){
            if ($validateParams){
                $username = $_POST["username"];
                $password = $_POST["password"];
                $isAdmin = $model->checkFullAdmin($username, $password);
                if ($isAdmin){
                    $update = $model->updateMonth($month);
                    $result = [
                        "status" => "success",
                        "message" => "month was updated"
                    ];
                }else{
                    $result = [
                        "status" => "error",
                        "message" => "permission denied"
                    ];
                }
            }else{
                $result = [
                    "status" => "success",
                    "message" => "username or password wasn't provided"
                ];
            }
            $this->view("json", $result);
        }
    }

    public function update_price(...$params){
        $model = $this->model('admin');
        $validateParams = Validator::validateElements($_POST, [
            'username',
            'password'
        ]);
        if ($params[0] && $validateParams){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $isAdmin = $model->checkFullAdmin($username, $password);
            if ($isAdmin){
                $update = $model->updatePrice($params[0]);
                $result = [
                    "status" => "success",
                    "message" => "successfuly changed month"
                ];
            }else{
                $result = [
                    "status" => "success",
                    "message" => "permission denied"
                ];
            }
        }else{
            if (!$validateParams){
                $result = [
                    'status' => "error",
                    "message" => "username or password wasn't provided"
                ];
            }else{
                $result = [
                    "status" => "error",
                    "message" => "param number one, wasn't provided"
                ];
            }
        }
        $this->view("json", $result);
    }

    public function change_privilege(...$params){
        $model = $this->model('admin');
        $result = [];
        if ($params[0] && $params[1]){
            $change = $model->changePrivilege($params[0], $params[1]);
            if ($change == OK){
                $result = [
                    "status" => "success"
                ];
            }else {
                $result = [
                    "status" => "error"
                ];
            }
            $this->view("json", $result);
        }
    }

    public function get_members(...$params){
        $model = $this->model('admin');
        if ($params[0] && $params[1]){
            echo "There are two params : {$params[0]}, {$params[1]}";
        }else{
            echo "there is nothing here.";
        }
    }

    public function add_charge_to_user(){
        $postParams = $_POST;
        $model = $this->model('admin');
        $isChecked = Validator::validateElements($postParams, [
            'username',
            'target_username',
            'year',
            'month'
        ]);
        if ($isChecked){
            $username = $_POST["username"];
            $targetUsername = $_POST["target_username"];
            $year = $_POST["year"];
            $month = $_POST["month"];
            $result = [
                "status" => "success",
                "message" => "charge added to $targetUsername"
            ];
        }else{
            $result = [
                "status" => "error",
                "message" => "something went wrong"
            ];
        }
        $this->view("json", $result);
    }

    public function remove_charge_from_user(){
        $username = $_POST["username"];
        $targetUsername = $_POST["target_username"];
        $postData = $_POST;
        $result = [];
        $year = $_POST["year"];
        $month = $_POST["month"];
        $model = $this->model('admin');
        $validateRequiredElements = Validator::validateElements($postData, [
            'username',
            'target_username',
            'year',
            'month'
        ]);
        if ($validateRequiredElements){
            $removeCharge = $model->removeChargeOfTheUser($targetUsername, $year, $month);
            if ($removeCharge == OK){
                $result = [
                    "status" => "success",
                    "message" => "charge removed"
                ];
            }else {
                $result = [
                    "status" => "error",
                    "message" => "charge didn't remove"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "you haven't provided needed parameters"
            ];
        }
        $this->view("json", $result);
    }

    public function add_mojtama_rules(){
        $postParams = $_POST;
        $model = $this->model('admin');
        $isChecked = Validator::validateElements($postParams, [
            'username',
            'password',
            'rule'
        ]);
        $result = [];
        if ($isChecked){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $rule = $_POST["rule"];
            $isOK = $model->addMojtamaRules($rule);
            if ($isOK){
                $result = [
                    "status" => "success",
                    "message" => "rule added"
                ];
            }else{
                $result = [
                    "status" => "error"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "You haven't specified inputs"
            ];
        }
        $this->view("json", $result);
    }

    public function add_mojtama_financial_status(){
        $postParams = $_POST;
        $validateRequiredParameters = Validator::validateElements($postParams, [
            'username',
            'password',
            'financial_json'
        ]);
        if ($validateRequiredParameters){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $financialStatusByJson = $_POST["financial_json"];
            $model = $this->model('admin');
            $model->addMojtamaFinancialStatus($financialStatusByJson);
            $result = [
                "status" => "success",
                "message" => "financial status added"
            ];
        }else{
            $result = [
                "status" => "error",
                "message" => "you haven't provided required parameters"
            ];
        }
        $this->view("json", $result);
    }

    public function remove_user($targetUsername=''){
        $result = [];
        $model = $this->model('admin');
        if (!empty($targetUsername)){
            $postParams = $_POST;
            $validateRequiredParams = Validator::validateElements($postParams, [
                'username',
                'password'
            ]);
            unset($postParams);
            if ($validateRequiredParams){
                $adminUsername = $_POST["username"];
                $adminPassword = $_POST["password"];
                $isAdmin = $model->checkFullAdmin($adminUsername, $adminPassword);
                if ($isAdmin){
                    $ans = $model->removeUser($targetUsername);
                    if ($ans){
                        $result = [
                            "status" => "success",
                            "message" => "user removed from database"
                        ];
                    }else{
                        $result = [
                            "status" => "error",
                            "message" => "user didn't removed"
                        ];
                    }
                }else{
                    $result = [
                        "status" => "error",
                        "message" => "permission denied"
                    ];
                }
            }else{
                $result = [
                    "status" => "error",
                    "message" => "you haven't provided required params"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "you haven't provided target username"
            ];
        }
        $this->view("json", $result);
    }

    public function users(){
        $model = $this->model("admin");
        $ans = $model->showInformationOfUsers();
        $this->view("json", $ans);
    }

}



?>
