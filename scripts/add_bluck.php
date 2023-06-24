<?php 
$dir = dirname(__FILE__);
require_once("$dir/../app/core/DB.php");
class BluckAdder extends DB {
    public function createYear() {
        $mostRecentYear = $this->getMostRecentYear();
        $blucks = $this->dbinformation->blucks;
        $year = intval($mostRecentYear) + 1;
        foreach($blucks as $bluck) {
            $sql = "CREATE TABLE `bluck{$bluck}_{$year}` (
              `واحد` int(2) NOT NULL,
              `محرم` int(11) NOT NULL,
              `صفر` int(11) NOT NULL,
              `ربیع الاول` int(11) NOT NULL,
              `ربیع الثانی` int(11) NOT NULL,
              `جمادی الاول` int(11) NOT NULL,
              `جمادی الثانی` int(11) NOT NULL,
              `رجب` int(11) NOT NULL,
              `شعبان` int(11) NOT NULL,
              `رمضان` int(11) NOT NULL,
              `شوال` int(11) NOT NULL,
              `ذیقعده` int(11) NOT NULL,
              `ذیحجه` int(11) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;";
            try{
                $this->query($sql);
            }catch(\Exception $e){
                echo $e;
            }
        }
    }

    public function getMostRecentYear() {
        $years = $this->getYears();
        $maxYear = 0;
        foreach($years as $year) {
            if ($year > $maxYear) {
                $maxYear = $year;
            }
        }
        return $maxYear;
    }

    public function getYears() {
        $years = [];
        $dbname = $this->dbinformation->dbname;
        $sql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA='$dbname' AND TABLE_NAME LIKE 'bluck%'";
        $query = $this->query($sql);
        $query = $query->get_result();
        if ($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                $table = $row["TABLE_NAME"];
                $year = preg_replace("/bluck[0-9]_/", "", $table);
                array_push($years, $year);
            }
        }else{
            return false;
        }
        $years = array_unique($years);
        rsort($years);
        return $years;
    }
}

$something = new BluckAdder;
$something->createYear();
