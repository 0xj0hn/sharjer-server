<?php 
class ChargeInfo{
    public $year;
    public $months;
    public function __construct($year, $months){
        $this->year = $year;
        $this->months = $months;
    }
    public static function fromJson($chargeDataArray){
        return new ChargeInfo($chargeDataArray[0], $chargeDataArray[1]);
    }
}


?>
