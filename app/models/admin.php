<?php 
class AdminModel extends Model{
    public function checkFullAdmin($username, $password){
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $result = $this->query($sql, "ss", $username, $password);
        $result = $result->get_result();
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()){
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
        require_once "app/models/user.php";
        $price = $price . "0";
        $sql = "UPDATE charge SET price = ?";
        $query = $this->query($sql, "s", $price);
        $userModel = new UserModel;
        $currentYear = $userModel->getThisYear();
        $currentMonth = $userModel->getThisMonth();
        $this->changeMonthsPrices($currentYear, [$currentMonth => intval($price)]);
        return $query ? true : false;
    }

    public function changePrivilege($target, $privilege){
        $sql = "";
        if ($privilege == "delete"){
            $sql = "DELETE FROM users WHERE username = '$target'";
            return $this->query($sql, "s", $target);
        }else{
            $sql = "UPDATE `users` SET `is_admin` = ? WHERE `users`.`username` = ?";
            $query = $this->query($sql, "ss", $privilege, $target);
            return $query ? true : false;
        }
        return ERROR;
    }

    public function getBluckMembers($adminuser, $bluck){
        $sql = "SELECT * FROM users WHERE `bluck`=$bluck ORDER BY `vahed`";
        $query = $this->query($sql);
        $query = $query->get_result();
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

    public function addChargeToTheUser($targetUsername, $year, $month){
        $userInfo= $this->getInformationOfTheUser($targetUsername);
        $bluck = $userInfo["bluck"];
        $vahed = $userInfo["vahed"];
        include_once "app/models/payment.php";
        $paymentModel = new PaymentModel;
        $chargePrice = $paymentModel->getChargeOfDate($year, $month);
        if ($chargePrice){
            return $paymentModel->payCharge((object)$userInfo, $chargePrice, $year, $month);
        }else{
            return false;
        }
    }

    public function removeChargeOfTheUser($targetUsername, $year, $month){
        $userInformation = $this->getInformationOfTheUser($targetUsername);
        $bluck = $userInformation["bluck"];
        $vahed = $userInformation["vahed"];
        $sql = "UPDATE bluck" . $bluck . "_" . $year . " SET `$month` = ? WHERE `واحد` = ?";
        $query = $this->query($sql, "si", 0, $vahed); //set zero as charge
        $this->setSumOfChargesInDB($targetUsername, $year);
        
        return $query ? OK : ERROR;
    }

    public function getInformationOfTheUser($targetUsername){
        $sql = "SELECT * FROM users WHERE username = ?";
        $query = $this->query($sql, "s", $targetUsername);
        $result = $query->get_result();
        $resultArray = [];
        if ($result->num_rows > 0){ // TODO: should be checked
            while ($row = $result->fetch_assoc()){
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
        $query = $this->query($sql);
        $query = $query->get_result();
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
        $query = $this->query($sql);
        $query = $query->get_result();
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

    public function showInformationOfUsers(){
        $sql = "SELECT * FROM users ORDER BY bluck, vahed ASC";
        $query = $this->query($sql);
        $query = $query->get_result();
        $rows = [];
        if ($query->num_rows > 0){
            while($row = $query->fetch_object()){
                $rows[] = $row;
            }
            return $rows;
        }else{
            return false;
        }
    }

    public function changeMonthsPrices($year, $months){
        $chargePricesContentDecoded = $this->getMonthsPrice();
        $chargePricesFileName = "chargePricesInfo.json";
        $chargePricesFile = fopen($chargePricesFileName, "w");
        $i = 0;
        foreach($months as $monthKey => $monthValue){
            if ($monthKey == $i){ //check if our months is indexed or associated
                foreach($monthValue as $month => $price){
                    $chargePricesContentDecoded[$year][$month] = $price;
                }
            }else{
                $chargePricesContentDecoded[$year][$monthKey] = $monthValue;
            }
            $i++;
        }
        $chargePricesContentEncoded = json_encode($chargePricesContentDecoded);
        fwrite($chargePricesFile, $chargePricesContentEncoded);
        fclose($chargePricesFile);
        return $chargePricesContentDecoded;
    }

    public function getMonthsPrice(){
        $chargePricesFileName = "chargePricesInfo.json";
        $chargePricesFile = fopen($chargePricesFileName, "r");
        $chargePricesContent = fread($chargePricesFile, filesize($chargePricesFileName));
        $chargePricesContentDecoded = json_decode($chargePricesContent, true);
        fclose($chargePricesFile);
        return $chargePricesContentDecoded;
    }

    public function sortPrices($monthsPriceArray){
        $pricesArr = [];
        $sortMonthsList = [
            "محرم",
            "صفر",
            "ربیع الاول",
            "ربیع الثانی",
            "جمادی الاول",
            "جمادی الثانی",
            "رجب",
            "شعبان",
            "رمضان",
            "شوال",
            "ذیقعده",
            "ذیحجه"
        ];
        $yearList = [];
        $retData = [];
        $monthsAndPrices = [];
        foreach($monthsPriceArray as $year => $monthAndPrice){
            $sortedMonths = $this->sortByArray($sortMonthsList, array_keys($monthAndPrice));
            foreach($sortedMonths as $i => $month){
                $yearList[$year][$month] = $monthAndPrice[$month];
            }
        }
        //foreach($)
        return $yearList;
    }

    public function sortByArray($sortArrElements, $goingToBeSortedArr){
        sort($goingToBeSortedArr);
        $temp = [];
        $visited = array_fill(0, count($goingToBeSortedArr), false);
        for($i = 0; $i < count($sortArrElements); $i++){
            $sortElement = $sortArrElements[$i];
            $firstOcc = array_search($sortElement, $goingToBeSortedArr);
            if($firstOcc === false){
                continue;
            }
            for($j = $firstOcc; $j < count($goingToBeSortedArr); $j++){
                if ($sortElement == $goingToBeSortedArr[$j]){
                    $temp[] = $sortElement;
                    $visited[$j] = true;
                }
            }
        }
        for($i = 0; $i < count($visited); $i++){
            if ($visited[$i] === false){
                $temp[] = $goingToBeSortedArr[$i];
            }
        }
        return $temp;
    }

    public function getUserInformation($username){
        $sql = "SELECT * FROM users WHERE username = ?";
        $query = $this->query($sql, "s", $username);
        $query = $query->get_result();
        while ($row = $query->fetch_object()){
            return $row;
        }
    }
}


?>
