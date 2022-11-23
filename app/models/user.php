<?php

class UserModel extends Model{
    public function signUp(
        $username, $password,
        $name, $family,
        $phoneNumber, $phoneNumber2,
        $bluck, $vahed,
        $family_members,
        $carPlate,
        $startDate='', $endDate='',
        $isAdmin, $isOwner
    ){
        $checkUser = $this->checkIfUserExists($username);
        if ($checkUser == DOES_NOT_EXIST){
            $sql = "INSERT INTO users (username, password, name, family, phone, phone2, bluck, vahed, family_members, car_plate, startdate, 
enddate, is_admin, is_owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $query = $this->query($sql, "ssssssiiissssi" //i: integer, s: string
                $username, $password,
                $name, $family,
                $phoneNumber, $phoneNumber2,
                $bluck, $vahed,
                $familyMembers,
                $carPlate,
                $startDate, $endDate,
                $isAdmin, $isOwner
            );
            return $query ? true : false;
        }else{
            return ERROR;
        }
    }

    public function checkIfUserExists($username){
        $sql = "SELECT * FROM users WHERE username = ?";
        $query = $this->query($sql, "s", $username);
        if ($query->num_rows == 0) return DOES_NOT_EXIST;
        return OK;
    }

    public function login(){

    }

}


?>
