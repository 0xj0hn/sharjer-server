<?php 

class PaymentModel extends Model {
    public function postToZibal($path, $parameters){
        $url = 'https://gateway.zibal.ir/v1/'.$path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

    /*
     * returns a string message based on status parameter from $_GET
     * @param $code
     * @return String
     */
    public function statusCodes($code){
        switch ($code) 
        {
            case -1:
                return "در انتظار پردخت";
            case -2:
                return "خطای داخلی";
            case 1:
                return "پرداخت شده - تاییدشده";
            case 2:
                return "پرداخت شده - تاییدنشده";
            case 3:
                return "لغوشده توسط کاربر";
            case 4:
                return "‌شماره کارت نامعتبر می‌باشد";
            case 5:
                return "‌موجودی حساب کافی نمی‌باشد";
            case 6:
                return "رمز واردشده اشتباه می‌باشد";
            case 7:
                return "‌تعداد درخواست‌ها بیش از حد مجاز می‌باشد";
            case 8:
                return "‌تعداد پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد";
            case 9:
                return "مبلغ پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد";
            case 10:
                return "‌صادرکننده‌ی کارت نامعتبر می‌باشد";
            case 11:
                return "خطای سوییچ";
            case 12:
                return "کارت قابل دسترسی نمی‌باشد";
            default:
                return "وضعیت مشخص شده معتبر نیست";
        }
    }

    public function isChargePaid($year, $month, $bluck, $vahed){
        $sql = "SELECT * FROM bluck$bluck" . "_$year " . "WHERE `واحد` = $vahed";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            while ($row = $query->fetch_assoc()){
                if ($row[$month] != "0"){
                    return true;
                }
            }
            return false;
        }
    }

    public function getCurrentPrice(){
        $sql = "SELECT sharge_price FROM price";
        $query = $this->mysql->query($sql);
        if ($query->num_rows > 0){
            while($row = $query->fetch_array()){
                return $row[0];
            }
        }
        return false;
    }

    public function payCharge($userInfo, $price=null, $year, $month){
        $bluck = $userInfo->bluck;
        $vahed = $userInfo->vahed;
        $sql = "UPDATE bluck$bluck"."_$year SET `$month` = '$price' WHERE `واحد` = '$vahed'";
        try{
            $prepare = $this->prepareRowForCharge($bluck, $vahed, $year);
            $update = $this->updateChargeMonth($bluck, $vahed, $year, $month, $price);
        }catch(Exception $e){
            $update = $this->updateChargeMonth($bluck, $vahed, $year, $month, $price);
        }
        $this->sumChargesUp($year, $bluck, $vahed);
        return true;
    }

    public function prepareRowForCharge($bluck, $vahed, $year){
        $sql = "INSERT INTO `bluck$bluck"."_$year` (`واحد`, `محرم`, `صفر`, `ربیع الاول`, `ربیع الثانی`, `جمادی الاول`, `جمادی الثانی`, `رجب`, `شعبان`, `رمضان`, `شوال`, `ذیقعده`, `ذیحجه`, `جمع`) VALUES ('$vahed', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0')";
        $query = $this->mysql->query($sql);
        return $query;
    }

    public function updateChargeMonth($bluck, $vahed, $year, $month, $price){
        $sql = "UPDATE bluck$bluck"."_$year SET `$month` = ? WHERE `واحد` = ?";
        $query = $this->query($sql, "si", $price, $vahed);
        return $query;
    }

    public function sumChargesUp($year, $bluck, $vahed){
        $sql = "SELECT * FROM bluck$bluck"."_$year WHERE `واحد` = ?";
        $query = $this->query($sql, "i", $vahed);
        $result = $query->get_result();
        $prices = [];
        $sumOfPrices = 0;
        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $rowLength = count($row);
                foreach($row as $key => $value){
                    if ($key == "واحد" || $key == "جمع") continue; //we split واحد from prices.
                    array_push($prices, $value);
                }
            }
            $sumOfPrices = array_sum($prices);
        }else{
            return false;
        }
        unset($query, $sql);
        $sql = "UPDATE bluck$bluck"."_$year SET `جمع` = ? WHERE `واحد` = ?";
        $query = $this->query($sql, "si", $sumOfPrices, $vahed);
        return $query ? true : false;
    }

    public function generatePriceByCustomChargeJson($userInfo, $jsonData, $infoFileContent){
        $prices = [];
        $bluck = $userInfo->bluck;
        $vahed = $userInfo->vahed;
        $sumOfPrices = 0;
        // $jsonDataDecoded must be like the following structure:
        //  [
        //      [
        //          year,
        //          [month, month2, month3]
        //      ],
        //      [...],
        //      [...]
        //  ]
        $jsonDataDecoded = json_decode($jsonData, true);
        $infoFileContentDecoded = json_decode($infoFileContent, true);
        sort($jsonDataDecoded);
        ksort($infoFileContentDecoded);
        $i = 0;
        foreach($jsonDataDecoded as $chargeData){
            $data = ChargeInfo::fromJson($chargeData);
            $year = $data->year;
            $months = $data->months;
            foreach($months as $month){
                $yearExists = array_key_exists($year, $infoFileContentDecoded);
                $monthsInInfoFile = array_keys($infoFileContentDecoded[$year]);
                $monthExists = array_search($month, $monthsInInfoFile);
                $chargePaid = $this->isChargePaid($year, $month, $bluck, $vahed);
                if ($yearExists && $monthExists !== false){
                    if (!$chargePaid){
                        $price = $infoFileContentDecoded[$year][$month];
                    }else{
                        $price = 0;
                    }
                    array_push($prices, $price);
                }else{
                    return false;
                }
            }
        }
        $sumOfPrices = array_sum($prices);
        return $sumOfPrices;
    }

    public function payCustomCharge($userInfo, $jsonData){
        $bluck = $userInfo->bluck;
        $vahed = $userInfo->vahed;
        $json = json_decode($jsonData);
        $monthsPaid = [];
        foreach($json as $chargeData){
            $charge = ChargeInfo::fromJson($chargeData);
            $year = $charge->year;
            $months = $charge->months;
            foreach($months as $month){
                $chargePaid = $this->isChargePaid($year, $month, $bluck, $vahed);
                if (!$chargePaid){
                    $chargePrice = $this->getChargeOfDate($year, $month);
                    $payment = $this->payCharge($userInfo, $chargePrice, $year, $month);
                    $sumChargesUp = $this->sumChargesUp($year, $bluck, $vahed);
                    array_push($monthsPaid, $month);
                }else{
                }
            }
        }
        $monthsPaidMessage = join("، ", array_map(function($month){
            return $month;
        }, $monthsPaid));
        return $monthsPaidMessage;
    }

    public function getChargeOfDate($year, $month){
        $infoFileName = "chargePricesInfo.json";
        $infoFile = fopen($infoFileName, "r");
        $infoFileContent = fread($infoFile, filesize($infoFileName));
        fclose($infoFile);
        $infoFileContentDecoded = json_decode($infoFileContent, true);
        $years = array_keys($infoFileContentDecoded);
        $yearExists = array_search($year, $years);
        if ($yearExists === false) return false;
        $monthsInInfoFile = array_keys($infoFileContentDecoded[$year]);
        $monthExists = array_search($month, $monthsInInfoFile);
        if (isset($monthExists)){
            $price = $infoFileContentDecoded[$year][$month];
            return $price;
        }else {
            return false;
        }
    }

}
?>
