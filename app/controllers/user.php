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

    public function app_version(){
        $model = $this->model("user");
        $res = $model->getApplicationVersion();
        $this->view("json", $res);
    }

}


?>
