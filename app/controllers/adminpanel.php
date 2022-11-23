<?php 

class AdminPanel extends Controller{

    public function update_month(...$params){
        $model = $this->model('admin');
        $month = $params[0];
        $isChecked = $model->checkEntryInputs($month);
        $update = $model->updateMonth($params[0]);
    }

    public function update_price(...$params){
        $model = $this->model('admin');
        if ($params[0]){
            $update = $model->updateMonth($params[0]);
            echo $update;
        }
    }

    public function change_privilege(...$params){
        $model = $this->model('admin');
        if ($params[0] && $params[1]){
            $change = $model->changePrivilege($params[0], $params[1]);
            echo $change;
        }
    }

    public function get_members(...$params){
        $model = $this->model('admin');
        if ($params[0] && $params[1]){
            //TODO here
        }
    }

    public function add_charge_to_user(){
        $username = $_POST["username"];
        $targetUsername = $_POST["target_username"];
        $year = $_POST["year"];
        $month = $_POST["month"];
        $model = $this->model('admin');
        $isChecked = $model->checkEntryElements($username, $year, $month);
        echo $isChecked;
    }

    public function remove_charge_from_user(){
        $username = $_POST["username"];
        $targetUsername = $_POST["target_username"];
        $year = $_POST["year"];
        $month = $_POST["month"];
        $model = $this->model('admin');
        if (isset($username) && isset($targetUsername) &&
            isset($year) && isset($month)
        ){
            $removeCharge = $model->removeChargeOfTheUser($targetUsername, $year, $month);
            if ($removeCharge == OK){
                $result = ['start' => 'end'];
                $this->view("json", $result);
            }
        }
    }

    public function add_mojtama_rules(){
        $username = $_POST["username"];
        $password = $_POST["password"];
        $rule = $_POST["rule"];
        $model = $this->model('admin');
        $isChecked = $model->checkEntryInputs($username, $password, $rule);
        $result = [];
        if ($isChecked == OK){
            $isOK = $model->addMojtamaRules($rule);
            if ($isOK){
                $result = [
                    "status" => "ok",
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
        $username = $_POST["username"];
        $password = $_POST["password"];
        $financialStatusByJson = $_POST["financial_json"];

        $model = $this->model('admin');
        $isChecked = $model->checkEntryInputs($username, $password, $financialStatus);
        if ($isChecked){
            $model->addMojtamaFinancialStatus($financialStatusByJson);
            $result = [
                "status" => "success",
                "message" => "financial status added"
            ];
        }else{
            $result = [
                "status" => "error",
                "message" => "You haven't specified inputs"
            ];
        }
        $this->view("json", $result);
    }

}



?>
