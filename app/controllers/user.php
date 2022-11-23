<?php

class User extends Controller{
    public function sign_up(){
        $username = $_POST["username"];
        $password = $_POST["password"];
        $name = $_POST["name"];
        $family = $_POST["family"];
        $phoneNumber = $_POST["phone1"];
        $phoneNumber2 = $_POST["phone2"];
        $bluck = $_POST["bluck"];
        $vahed = $_POST["vahed"];
        $familyMembers = $_POST["family_members"];
        $carPlate = $_POST["car_plate"];
        $startDate = $_POST["start_date"];
        $endDate = $_POST["end_date"];
        $isAdmin = $_POST["is_admin"];
        $isOwner = $_POST["is_owner"];

        $model = $this->model('admin');
        $checkEntries = $model->checkEntryInputs(
            $username,
            $password,
            $name,
            $family,
            $phoneNumber,
            $phoneNumber2,
            $bluck,
            $vahed,
            $familyMembers,
            $carPlate,
            $startDate, //TODO: i have to ask whether make startdate and enddate required or not!
        );
        unset($model);

        if ($checkEntries){
            $model = $this->model('user');
            $model->signUp(
                $username, $password,
                $name, $family,
                $phoneNumber, $phoneNumber2,
                $bluck, $vahed,
                $familyMembers,
                $carPlate,
                $startDate, $endDate,
                $isAdmin, $isOwner
            );
        }

    }
}


?>
