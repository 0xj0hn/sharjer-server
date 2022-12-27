<?php

class UserModel extends Model{
    public function getUserInformation($username, $password){
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $query = $this->query($sql, "ss", $username, $password);
        $result = $query->get_result();
        $retValue = [];
        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $retValue = $row;
            }
        }else{
            return 0;
        }
        return (object)$retValue;
    }
    public function getFinancialStatus(){
        $fileName = "financial_status.json";
        $file = fopen($fileName, "r");
        $fileContents = fread($file, filesize($fileName));
        return $fileContents;
    }
    
    public function getMojtamaRules(){
        $sql = "SELECT * FROM mojtama_rules";
        $query = $this->mysql->query($sql);
        $resultArray = [];
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                array_push($resultArray, $row);
            }
        }else{
            return false;
        }
        return $resultArray;
    }

    public function peopleWhoChargedThisMonth(){
        $blucks = $this->dbinformation->blucks;
        $thisYear = $this->getThisYear();
        $thisMonth = $this->getThisMonth();
        $peopleWhoCharged = 0;
        $bluckMembersCharges = [];
        foreach($blucks as $bluck){
            $bluckCharges = 0;
            $getPeopleWhoChargedInThisBluck = $this->getPeopleWhoChargedInBluck($bluck, $thisYear, $thisMonth);
            //we want to get people splitted(by bluck or may whole of blucks)
            $bluckCharges += $getPeopleWhoChargedInThisBluck;
            $peopleWhoCharged += $getPeopleWhoChargedInThisBluck;

            array_push($bluckMembersCharges, $bluckCharges);
        }
        return [
            "bluckData" => $bluckMembersCharges,
            "people_who_charged" => $peopleWhoCharged,
            "year" => $thisYear,
            "month" => $thisMonth
        ];
    }

    public function getPeopleWhoChargedInBluck($bluckNumber, $year, $month){
        $sql = "SELECT COUNT(*) FROM bluck$bluckNumber" . "_$year WHERE NOT `$month` = '0'";
        $query = $this->mysql->query($sql);
        $sumOfPeople = 0;
        while($row = $query->fetch_array()){
            $sumOfPeople += $row[0];
        }
        return $sumOfPeople;
    }

    public function getThisYear(){
        $years = $this->getYears();
        $maxYear = 0;
        foreach($years as $year){
            if ($year > $maxYear){
                $maxYear = $year;
            }
        }
        $thisYear = $maxYear;
        return $thisYear;
    }

    public function getThisMonth(){
        $sql = "SELECT month FROM charge";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            while($row = $query->fetch_array()){
                return $row[0];
            }
        }else{
            return false;
        }
    } 

    public function getYears(){
        $years = [];
        $dbname = $this->dbinformation->dbname;
        $sql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA='$dbname'";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                if (str_contains($row["TABLE_NAME"], "bluck")){
                    $table = $row["TABLE_NAME"];
                    $year = preg_replace("/bluck[0-9]_/", "", $table);
                    array_push($years, $year);
                }
            }
        }else{
            return false;
        }
        $years = array_unique($years);
        rsort($years);
        return $years;
    }

    public function getApplicationVersion(){
        $sql = "SELECT * FROM app_version";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                return $row;
            }
        }else{
            return false;
        }
    }

    public function getUserPayStat($bluck, $vahed){
        $years = $this->getYears();
        $results = [];
        foreach($years as $year){
            $sql = "SELECT * FROM bluck$bluck"."_$year WHERE `واحد` = ?";
            $query = $this->query($sql, "i", $vahed);
            $result = $query->get_result();
            while($row = $result->fetch_assoc()){
                array_push($results, ["$year" => $row]);
            }
        }
        return $results;
    }
}


?>
