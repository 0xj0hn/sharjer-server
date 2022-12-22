<?php 
class AdminModel extends Model{
    public function checkFullAdmin($username, $password){
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $res = $this->query($sql, "ss", $username, $password)->get_result();
        if ($res->num_rows > 0){
            while ($row = $res->fetch_assoc()){
                if ($row["is_admin"] == "full"){
                    return true;
                }else{
                    return false;
                }
            }
        }
        return false;
    }

    public function updateMonth($month){
        $sql = "UPDATE charge SET month = ?";
        $res = $this->query($sql, "s", $month);
        return $res ? true : false;
    }

    public function updatePrice($price){
        $price = $price . "0";
        $sql = "UPDATE charge SET price = ?";
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
        $sql = "SELECT * FROM users WHERE username = ?";
        $query = $this->query($sql, "s", $targetUsername);
        $result = $query->get_result();
        $resultArray = [];
        if ($result->num_rows > 0){ // TODO: should be checked
            while ($row = $query->fetch_assoc()){
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

    public function addMojtamaRules($inputRuleFromAdmin){
        $currentTime = time();
        $doesRuleExist = $this->checkIfRuleHasAdded();
        if ($doesRuleExist == OK){
            $query = $this->editMojtamaRules($inputRuleFromAdmin);
            return $query ? OK : ERROR;
        }
        
        $sql = "INSERT INTO mojtama_rules (rule, created_at) VALUES (?, ?)";
        $query = $this->query($sql, "si", $inputRuleFromAdmin, $currentTime);
        return $query ? OK : ERROR;
    }

    public function checkIfRuleHasAdded(){
        $sql = "SELECT * FROM mojtama_rules";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            return OK;
        }else{
            return DOES_NOT_EXIST;
        }
    }
    
    public function editMojtamaRules($inputRuleFromAdmin){
        $currentTime = time();
        $sql = "UPDATE mojtama_rules SET rule = ?, edited_at = ?";
        $query = $this->query($sql, "sd", $inputRuleFromAdmin, $currentTime);
        return $query;
    }

    public function addMojtamaFinancialStatus($jsonFinancialStatus){
        // $jsonFinancialStatus:
        // [
        //  {'title': 'test', 'price':'100000'},
        //  {'title': 'test2', 'price': '200000'}
        // ]
        $fileName = "financial_status.json";
        $file = fopen($fileName, "w");
        fwrite($file, json_encode($jsonFinancialStatus));
        fclose($file);
    }

    public function removeUser($username){
        $sql = "DELETE FROM `users` WHERE `users`.`username` = ?";
        $query = $this->query($sql, "s", $username);
        return $query ? true : false;
    }


}


?>
