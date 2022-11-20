<?php 

class AdminPanel extends Controller{

    public function update_month(...$params){
        $model = $this->model('admin');
        $month = $params[0];
        $isChecked = $model->checkEntryElements($month);
        $update = $model->update_month($params[0]);

    }

    public function update_price(...$params){
        $model = $this->model('admin');
        if ($params[0]){
            $update = $model->update_month($params[0]);
            echo $update;
        }
    }

    public function change_privilege(...$params){
        $model = $this->model('admin');
        if ($params[0] && $params[1]){
            $change = $model->change_privilege($params[0], $params[1]);
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
            $remove_charge = $model->removeChargeOfTheUser($targetUsername, $year, $month);
            if ($remove_charge == OK){
                $result = ['start' => 'end'];
                $view = $this->view("json", $result);
            }
        }
    }

}



?>
