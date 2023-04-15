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

    public function updateUserInformation($postData){
        $sql = "UPDATE users SET username = ?, name = ?,
            family = ?,
            phone = ?,
            phone2 = ?,
            bluck = ?,
            vahed = ?,
            family_members = ?,
            car_plate = ?,
            startdate = ?,
            enddate = ?,
            is_owner = ? WHERE username = ? AND password = ?";
        $query = $this->query($sql,
            "sssssiiisssiss",
            $postData["new_username"],
            $postData["name"],
            $postData["family"],
            $postData["phone"],
            $postData["phone2"],
            $postData["bluck"],
            $postData["vahed"],
            $postData["family_members"],
            $postData["car_plate"],
            $postData["startdate"],
            $postData["enddate"],
            $postData["is_owner"],
            $postData["username"],
            $postData["password"]
        );
        return $query;
    }

    public function getFinancialStatus(){
        $fileName = "financial_status.json";
        $file = fopen($fileName, "r");
        $fileContents = fread($file, filesize($fileName));
        return $fileContents;
    }
    
    public function getMojtamaRules(){
        $sql = "SELECT * FROM mojtama_rules";
        $query = $this->query($sql);
        $query = $query->get_result();
        $resultArray = [];
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                $resultArray[] = $row;
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
        $query = $this->query($sql);
        $query = $query->get_result();
        $sumOfPeople = 0;
        while($row = $query->fetch_array()){
            $sumOfPeople += $row[0];
        }
        return $sumOfPeople;
    }

    public function getThisYear(){
        //using request to an api:
        $currentTimestamp = time();
        $date = date("d-m-Y", $currentTimestamp);
        $returnedJson = file_get_contents("http://api.aladhan.com/v1/gToH/$date");
        $returnedJson = json_decode($returnedJson, true);
        if (array_key_exists("code", $returnedJson) && $returnedJson["code"] == 200){
            $thisYear = $returnedJson["data"]["hijri"]["year"];
            return $thisYear;
        } //if the api method doesn't work, it will use the most updated year in DB.
        //using the most updated year table
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
        $query = $this->query($sql);
        $query = $query->get_result();
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
        $query = $this->query($sql);
        $query = $query->get_result();
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                if (strpos($row["TABLE_NAME"], "bluck")){
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
        $query = $this->query($sql);
        $query = $query->get_result();
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
                $yearWrapperArr = array($year => $row);
                $results[] = $yearWrapperArr;
            }
        }
        return $results;
    }
}


?>
