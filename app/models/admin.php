<?php 
class AdminModel extends Model{
    public function getAdmin(){
        return "admin";
    }
    
    public function checkEntryElements(...$params){
        $checkedValues = [];
        foreach($params as $key){
            if (isset($key) && !empty($key)){
                array_push($checkedValues, $key);
            }
        }
        if (count($checkedValues) == count($params)){
            return OK;
        }else{
            return ERROR;
        }
    }

    public function checkFullAdmin($username){
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $res = $this->mysql->query($sql);
        while ($row = $res->fetch_assoc()){
            if ($row["is_admin"] == "full"){
                return OK;
            }else{
                return ERROR;
            }
        }
        return DOES_NOT_EXIST;
    }

    public function updateMonth($month){
        $sql = "UPDATE charge SET month = ?";
        $res = $this->query($sql, "s", $month);
        return $res ? true : false;
    }

    public function changePrice($price){
        $price = $price . "0";
        $sql = "UPDATE price SET `sharge_price` = ?";
        $query = $this->query($sql, "s", $price);
        return $query ? true : false;
    }

    public function changePrivilege($target, $privilege){
        $sql = "";
        $types = "";
        if ($privilege == "delete"){
            $sql = "DELETE FROM users WHERE username = '$target'";
            return $this->query($sql, "s", $target);
        }else{
            $sql = "UPDATE `users` SET `is_admin` = ? WHERE `users`.`username` = ?";
            return $this->query($sql, "ss", $privilege, $target);
        }
        return ERROR;
    }

    public function getBluckMembers($adminuser, $bluck){
        $sql = "SELECT * FROM users WHERE `bluck`=$bluck ORDER BY `vahed`";
        $query = $this->mysql->query($sql);
        $arr = array();
        if ($query->num_rows > 0){
            while ($row = $query->fetch_assoc()){
                if ($row["is_admin"] == "full"){
                    continue;
                }
                array_push($arr,$row);
            }
        }
        return $arr;
    }

    public function removeChargeOfTheUser($targetUsername, $year, $month){
        $userInformation = $this->getInformationOfTheUser($targetUsername);
        $bluck = $userInformation["bluck"];
        $vahed = $userInformation["vahed"];
        $sql = "UPDATE bluck" . $bluck . "_" . $year . " SET `$month` = ?";
        $query = $this->query($sql, "s", 0); //set zero as charge
        $this->setSumOfChargesInDB($targetUsername, $year);
        
        return $query ? OK : ERROR;
    }

    public function getInformationOfTheUser($targetUsername){
        $sql = "SELECT * FROM users WHERE username = '$targetUsername'";
        $query = $this->mysql->query($sql);
        $resultArray = [];
        if ($query->num_rows > 0){
            while ($row = $query->fetch_assoc()){
                //row contains these keys: 
                //"username", "password",
                //"name", "family","phone",
                //"bluck", "vahed", "startdate", "enddate"
                return $row; 
            }
        }else{
            return DOES_NOT_EXIST;
        }
    }

    public function setSumOfChargesInDB($targetUsername, $year){
        $userInformation = $this->getInformationOfTheUser($targetUsername);
        $bluck = $userInformation["bluck"];
        $vahed = $userInformation["vahed"];
        $sql = "SELECT * FROM bluck$bluck" . "_$year" . " WHERE  `واحد` = $vahed";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                $arrayKeys = array_keys($row);
                $pricesArray = [];
                for ($i = 0; $i < count($arrayKeys); $i++){
                    $lastIndex = count($arrayKeys) - 1;
                    if ($i == 0 || $i == $lastIndex) continue; //the first and last index will never use.
                    $arrayKeyByIndex = $arrayKeys[$i];
                    array_push($pricesArray, $row[$arrayKeyByIndex]);
                }
                $sumOfTheWholeCharge = array_sum($pricesArray);
                $sql = "UPDATE `bluck$bluck"."_$year` " . "SET `جمع` = ? WHERE `واحد` = $vahed";
                $query2 = $this->query($sql, "s", $sumOfTheWholeCharge);
            }
        }
    }




}


?>
