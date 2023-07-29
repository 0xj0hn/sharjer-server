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

    public function getEncryptedPassword($plainPassword){
        return $this->encrypt($plainPassword);
    }

    public function updateYear($year) {
        $sql = "UPDATE charge SET year = ?";
        $result = $this->query($sql, "i", $year);
        return $result ? true : false;
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
        //  ['title' => 'price'],
        //  ['title2' => 'price2']
        // ]
        //$jsonFinancialStatus = $this->generateFinancialStatus($jsonFinancialStatus);
        $fileName = "financial_status.json";
        $file = fopen($fileName, "w");
        fwrite($file, json_encode($jsonFinancialStatus));
        fclose($file);
    }

    public function generateFinancialStatus($jsonFinancialStatus, $chargesPaidPrice=0) {
        $decodedFinancialStatus = json_decode($jsonFinancialStatus, true);
        $remainingPrice = $chargesPaidPrice;
        $chargePaidKey = "* جمع شارژ پرداختی ماه *";
        $remainingKey = "- باقی مانده -";
        $decodedFinancialStatus = array_filter($decodedFinancialStatus, fn ($element) =>
            array_keys($element)[0] != $chargePaidKey && array_keys($element)[0] != $remainingKey, //checks if title isn't $chargePaidKey or $remainingKey
        );
        foreach($decodedFinancialStatus as $financialStatusElement) {
            foreach($financialStatusElement as $title => $price) {
                $price = (int)$price;
                $remainingPrice = $remainingPrice - $price;
            }
        }
        array_unshift($decodedFinancialStatus, [
            $chargePaidKey => (string)$chargesPaidPrice
        ]); //put at the beginning
        $decodedFinancialStatus[] = [
            $remainingKey => (string)$remainingPrice
        ]; //put at the end
        return json_encode($decodedFinancialStatus);
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
        $sortedPricesByKeys = krsort($chargePricesContentDecoded);
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

    public function pushCsvDataToDatabase($csvFileName){
        $csvFilePointer = fopen($csvFileName, "r");
        $separatedFilename = explode("_", pathinfo($csvFileName, PATHINFO_FILENAME));
        $bluck = $separatedFilename[0];
        $year = $separatedFilename[1];
        $isPrepared = $this->createTableIfNotExist($bluck, $year);
        $isInserted = false;
        if ($isPrepared) {
            $givenVahed = 1; //the line number is the user's vahed
            while(!feof($csvFilePointer)) {
                $row = fgetcsv($csvFilePointer);
                $dbLikeChargeData = $this->generateDBLikeChargeData($row);
                if (!empty($row)) {
                    $isInserted = $this->insertCsvChargeData($bluck, $givenVahed, $year, $dbLikeChargeData);
                }
                $givenVahed++;
            }
        }
        return $isInserted;
    }

    public function generateDBLikeChargeData($row){
        $zeroCounts = 0; // indicate the number of the zeros in a row of charges.
        $zeroIndex = 0;
        $tempRow = $row;
        if ($row == false) return;
        foreach($row as $index => $paidCharge) {
            if ($paidCharge == "0") {
                $zeroCounts++;
                if ($index - 1 == $zeroIndex) {
                    continue;
                }
                $zeroIndex = $index;
            }
            else if ($zeroCounts > 0) {
                $dividedCharge = (string)intval($paidCharge / ($zeroCounts + 1));
                for ($i = $index; $i >= $zeroIndex; $i--) {
                    $row[$i] = $dividedCharge;
                    unset($tempRow[$zeroIndex]);
                }
                $zeroCounts = 0; //reset variable
            }
            else if ($paidCharge == "") {
                $row[$index] = "0";
            }else{
                $row[$index] = $paidCharge;
            }
        }
        return $row;
    }

    public function createTableIfNotExist($bluck, $year){
        $dbname = $this->dbinformation->dbname;
        $sql = "DROP TABLE IF EXISTS `{$dbname}`.`{$bluck}_{$year}`";
        $this->query($sql);
        $sql = "CREATE TABLE IF NOT EXISTS `{$dbname}`.`{$bluck}_{$year}` (
            `واحد` int(2) PRIMARY KEY,
            `محرم` int(11),
            `صفر` int(11),
            `ربیع الاول` int(11),
            `ربیع الثانی` int(11),
            `جمادی الاول` int(11),
            `جمادی الثانی` int(11),
            `رجب` int(11),
            `شعبان` int(11),
            `رمضان` int(11),
            `شوال` int(11),
            `ذیقعده` int(11),
            `ذیحجه` int(11),
            `جمع` int(11)
        )";
        $query = $this->query($sql);
        return $query ? true : false;
    }

    public function insertCsvChargeData($bluck, $vahed, $year, $chargeData) {
        $sql = "INSERT INTO {$bluck}_{$year} VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sumOfCharges = array_sum($chargeData);
        $chargeData[] = $sumOfCharges;
        $query = $this->query($sql, "ssssssssssssss", $vahed, ...$chargeData);
        return $query;
    }
}


?>
