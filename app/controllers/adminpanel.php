<?php 

class AdminPanel extends Controller{

    public function update_month(...$params){
        $model = $this->model('admin');
        $notifModel = $this->model('notification');
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
                    $sendNotif = $notifModel->sendNotifToAllMembersOnChargeTime();
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
        $validation = Validator::validateElements($_POST, [
            "username",
            "password"
        ]);
        $validation2 = Validator::validateElements($params, [0, 1]);
        $validation = ($validation && $validation2);
        if ($validation){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $targetUsername = $params[0];
            $privilege = $params[1];
            $checkFullAdmin = $model->checkFullAdmin($username, $password);
            $change = $model->changePrivilege($targetUsername, $privilege);
            if ($change == OK && $checkFullAdmin == true){
                $result = [
                    "status" => "success",
                    "message" => "privilege has been changed"
                ];
            } else if ($checkFullAdmin != true){
                $result = [
                    "code" => 401,
                    "status" => "unathorized_user",
                    "message" => "Permission denied"
                ];
            } else {
                $result = [
                    "status" => "error"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $result);
    }

    public function add_charge_to_user(){
        $postParams = $_POST;
        $model = $this->model('admin');
        $isChecked = Validator::validateElements($postParams, [
            'username',
            'password',
            'target_username',
            'year',
            'month'
        ]);
        if ($isChecked){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $isAdmin = $model->checkFullAdmin($username, $password);
            if ($isAdmin){
                $targetUsername = $_POST["target_username"];
                $year = $_POST["year"];
                $month = $_POST["month"];
                $model->addChargeToTheUser($targetUsername, $year, $month);
                $result = [
                    "status" => "success",
                    "message" => "charge added to $targetUsername"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "something went wrong"
            ];
        }
        $this->view("json", $result);
    }

    public function add_multiple_charge_to_user(){
        $results = [];
        $model = $this->model("admin");
        $validate = Validator::validateElements($_POST, [
            "username",
            "password",
            "target_username",
            "json"
        ]);
        if ($validate){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $isAdmin = $model->checkFullAdmin($username, $password);
            if ($isAdmin){
                $targetUsername = $_POST["target_username"];
                $jsonValue = $_POST["json"];
                $decodedJson = json_decode($jsonValue, true);
                $year = $decodedJson[0];
                $monthsArr = $decodedJson[1];
                $monthsAddedArr = [];
                foreach($monthsArr as $month){
                    $isChargeAdded = $model->addChargeToTheUser($targetUsername, $year, $month); //TODO
                    if ($isChargeAdded){
                        $monthsAddedArr[] = $month;
                    }
                }
                $results = [
                    "status" => "success",
                    "message" => "charges were added",
                    "months" => implode(", ", $monthsAddedArr)
                ];
            }else{
                $results = [
                    "status" => "error",
                    "message" => "permission denied"
                ];
            }
        }else{
            $results = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view('json', $results);
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

    public function change_months_price(){
        $model = $this->model("admin");
        $validate = Validator::validateElements($_POST, [
            "username",
            "password",
            "year",
            "months" //month and price should be here.
        ]);
        $ans = [];
        if ($validate){
            $year = $_POST["year"];
            $months = json_decode($_POST["months"], true);
            $ans = $model->changeMonthsPrices($year, $months);
        }else{
            $ans = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $ans);
    }

    public function months_price(){
        $model = $this->model("admin");
        $result = $model->getMonthsPrice();
        $sortedArr = $model->sortPrices($result);
        $result = $sortedArr;
        $this->view("json", $result);
    }

    public function get_charge_status_of($targetUsername=null){
        $result = [];
        $model = $this->model("admin");
        $userModel = $this->model("user");
        $validation = Validator::validateElements($_POST, [
            "username",
            "password"
        ]);
        $validation = true; //TODO
        if ($validation){
            if ($targetUsername){
                $userInfo = $model->getUserInformation($targetUsername);
                if ($userInfo == null) return; //exit when user doesn't exist
                $result = $userModel->getUserPayStat($userInfo->bluck, $userInfo->vahed);
            }else{
                $result = [
                    "status" => "error",
                    "message" => "you haven't provided username"
                ];
            }
        }else {
            $result = [
                "code" => 401,
                "status" => "error",
                "message" => "authentication failed"
            ];
        }
        $this->view("json", $result);
    }

    public function send_notif($title){
        $model = $this->model("admin");
        $notifModel = $this->model("notification");
        $validation = Validator::validateElements($_POST, [
            "username",
            "password",
            "body"
        ]);
        $result = [];
        if ($validation){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $body = $_POST["body"];
            $adminCheck = $model->checkFullAdmin($username, $password);
            if ($adminCheck){
                $notifModel = $notifModel->sendNotifToAllMembers($title, $body);
                if ($notifModel) {
                    $result = [
                        "status" => "success",
                        "message" => "message sent to all members"
                    ];
                }else{
                    $result = [
                        "status" => "error",
                        "message" => "request had problems"
                    ];
                }
            }else{
                $result = [
                    "code" => 401,
                    "status" => "error",
                    "message" => "permission denied"
                ];
            }
        }else{
            $result = [
                "code" => 400,
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $result);
    }

    public function csv_uploader(){
        $this->view("csv_uploader");
    }

    public function csv_uploader_response(){
        $model = $this->model("admin");
        $validate = Validator::validateElements($_POST, [
            "username",
            "password",
        ]);
        $validate = ($validate && Validator::validateElements($_FILES, [
            "csv_file"
        ]));
        if ($validate){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $encryptedPassword = $model->getEncryptedPassword($password);
            $checkFullAdmin = $model->checkFullAdmin($username, $encryptedPassword);
            if ($checkFullAdmin){
                $csvFileInfo = $_FILES["csv_file"];
                $csvFileName = $csvFileInfo["name"];
                move_uploaded_file($csvFileInfo["tmp_name"], basename($csvFileName));
                $isPushed = $model->pushCsvDataToDatabase($csvFileName);
                if ($isPushed){
                    $this->view("payment_success", "شارژ‌ها به پایگاه داده اضافه شدند.");
                }
                unlink($csvFileInfo["name"]);
            }else{
                $this->view("payment_error", "شکست", "شما دسترسی این کار را ندارید.");
            }
        }else{
            $this->view("payment_error", "شکست", "بعضی مقادیر از سمت شما ارسال نشدند. لطفا دوباره امتحان کنید.");
        }

    }

    public function create_year($year=null) {
        $result = [];
        if ($year == null || intval($year) == 0) {
            $result = [
                "status" => "error",
                "message" => "you haven't provided `year`"
            ];
        }else{
            $model = $this->model('payment');
            $blucks = $model->dbinformation->blucks;
            foreach($blucks as $bluck) {
                $model->createTableIfNotExists($bluck, $year);
            }
            $result = [
                "status" => "success",
                "message" => "year has been added to database"
            ];
        }
        $this->view("json", $result);
    }
}



?>
