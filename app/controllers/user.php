<?php

class User extends Controller{
    public function get_financial_status(){
        $result = [];
        $model = $this->model('user');
        $ans = $model->getFinancialStatus();
        if ($ans){
            $jsonAsList = json_decode($ans, true);
            $result = [
                "status" => "success",
                "data" => $jsonAsList
            ];
        }else{
            $result = [
                "status" => "error",
                "message" => "something went wrong"
            ];
        }
        $this->view("json", $result);
    }

    public function get_mojtama_rules(){
        $model = $this->model('user');
        $ans = $model->getMojtamaRules();
        if ($ans){
            $result = [
                "status" => "success",
                "data" => $ans
            ];
        }else{
            $result = [
                "status" => "error",
            ];
        }
        $this->view("json", $result);
    }

    public function people_who_charged_this_month(){
        $model = $this->model('user');
        $answerArray = $model->peopleWhoChargedThisMonth();
        $year = $answerArray["year"];
        $month = $answerArray["month"];
        $peopleWhoCharged = $answerArray["people_who_charged"];
        $bluck1Charges = $answerArray["bluckData"][0]; //this is by index. the first index is for bluck1 and etc.
        $bluck2Charges = $answerArray["bluckData"][1];
        $bluck3Charges = $answerArray["bluckData"][2];
        $result = [
            "status" => "success",
            "data" => "در بلوک۱، $bluck1Charges نفر شارژ خود را پرداخت کرده است.
در بلوک۲، $bluck2Charges نفر شارژ خود را پرداخت کرده است.
در بلوک۳، $bluck3Charges نفر شارژ خود را پرداخت کرده است."
        ];
        $this->view("json", $result);
    }

    public function app_version($givenAppVersion){
        $model = $this->model("user");
        $res = [];
        $latestVersionInfo = $model->getApplicationVersion();
        if (isset($givenAppVersion) && !empty($givenAppVersion)){
            if ($latestVersionInfo["version_number"] == $givenAppVersion){
                $res = array_merge(["status" => "updated", "given_version" => $givenAppVersion], $latestVersionInfo);
            }else{
                $res = array_merge(["status" => "not updated", "given_version" => $givenAppVersion], $latestVersionInfo);
            }
        }else{
            $res = $latestVersionInfo;
        }
        $this->view("json", $res);
    }

    public function user_pay_stat(){
        $model = $this->model("user");
        $result = [];
        $validate = Validator::validateElements($_POST, [
            "username",
            "password"
        ]);
        if ($validate){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $userInfo = $model->getUserInformation($username, $password);
            if (!$userInfo){
                $result = [
                    "status" => "error",
                    "message" => "login failed"
                ];
            }else{
                $bluck = $userInfo->bluck;
                $vahed = $userInfo->vahed;
                $result = $model->getUserPayStat($bluck, $vahed);
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $result);
    }

    public function get_years(){
        $model = $this->model('user');
        $years = $model->getYears();
        $this->view("json", $years);
    }

    public function get_month(){
        $model = $this->model('user');
        $year = $model->getThisYear();
        $month = $model->getThisMonth();
        $result = [
            "status" => "success",
            "month" => "سال $year\n$month"
        ];
        $this->view("json", $result);
    }

    public function get_myself(){
        $model = $this->model("user");
        $result = [];
        $isValidated = Validator::validateElements($_POST, [
            "username",
            "password"
        ]);
        if ($isValidated){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $userInfo = $model->getUserInformation($username, $password);
            if ($userInfo === 0){
                $result = [
                    "status" => "error",
                    "message" => "user not found"
                ];
            }else{
                $result = [
                    "status" => "success",
                    "data" => $userInfo
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

    public function update_myself(){
        $model = $this->model("user");
        $fields = [
            "username",
            "password",
            "name",
            "family",
            "phone",
            "phone2",
            "bluck",
            "vahed",
            "family_members",
            "car_plate",
            "startdate",
            "enddate",
            "is_owner"
        ];
        $validation = Validator::validateElements($_POST, $fields);
        $result = [];
        if ($validation){
            $model->updateUserInformation($_POST);
            $result = [
                "status" => "success",
                "message" => "user information has been updated"
            ];
        }else{
            $result = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $result);
    }

    public function update_firebase_token(){
        $model = $this->model("notification");
        $userModel = $this->model("user");
        $validation = Validator::validateElements($_POST, [
            "username",
            "password",
            "new_token"
        ]);
        $response = [];
        if ($validation){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $newToken = $_POST["new_token"];
            $userInfo = $userModel->getUserInformation($username, $password);
            if ($userInfo === 0){
                $response = [
                    "status" => "error",
                    "message" => "user doesn't exist"
                ];
            }else{
                $updated = $model->updateFirebaseToken($username, $newToken);
                if ($updated){
                    $response = [
                        "status" => "success",
                        "message" => "device token has been updated"
                    ];
                }else{
                    $response = [
                        "status" => "error",
                        "message" => "due to a problem i couldn't update the token"
                    ];
                }
            }
        }else{
            $response = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $response);
    }

    public function change_password(){
        $result = [];
        $validate = Validator::validateElements($_POST, [
            "username",
            "password",
            "new_password"
        ]);
        if ($validate){
            $model = $this->model("user");
            $username = $_POST["username"];
            $password = $_POST["password"];
            $newPassword = $_POST["new_password"];
            $isUpdated = $model->changePassword($username, $password, $newPassword);
            if ($isUpdated) {
                $result = [
                    "status" => "success",
                    "message" => "password has been changed"
                ];
            }else{
                $result = [
                    "status" => "error",
                    "message" => "error has been occured while updating the password"
                ];
            }
        } else {
            $result = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
        $this->view("json", $result);
    }
}


?>
