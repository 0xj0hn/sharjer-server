<?php 
define("ZIBAL_MERCHANT_KEY", "zibal");
class Payment extends Controller {

    public function simple_request(){
        $getParams = $_POST;
        $validate = Validator::validateElements($getParams, [
            'username',
            'password',
            'year',
            'month'
        ]);
        unset($getParams);
        if ($validate){
            $username = $_POST["username"];
            $password = $_POST["password"];
            $model = $this->model('payment');
            $userModel = $this->model('user');
            $currentPrice = $model->getCurrentPrice();
            $currentYear = $userModel->getThisYear();
            $currentMonth = $userModel->getThisMonth();
            $userInformation = $userModel->getUserInformation($username, $password);
            if (!$userInformation){
                $result = [
                    "status" => "error",
                    "message" => "user or password isn't correct!"
                ];
                $this->view("json", $result);
                return;
            }
            $bluck = $userInformation->bluck;
            $vahed = $userInformation->vahed;
            $phoneNumber = $userInformation->phone;
            $parameters = array(
                "merchant"=> ZIBAL_MERCHANT_KEY,//required
                "callbackUrl" => "http://localhost/mojtama-server-mvc/payment/callback/?username=".$username."&month=".$currentMonth."&year=".$currentYear,
                "amount"=> $currentPrice,//required
                "orderId"=> time(),//optional
                "mobile"=> $phoneNumber//optional for mpg
            );
            $isChargePaid = $model->isChargePaid($currentYear, $currentMonth, $bluck, $vahed);
            if (!$isChargePaid){
                $response = $model->postToZibal("request", $parameters);
                if ($response->result == 100){
                    $startGatewayUrl = "https://gateway.zibal.ir/start/".$response->trackId;
                    $result = [
                        "status" => "success",
                        "url" => $startGatewayUrl
                    ];
                }else{
                    $result = [
                        "status" => "error"
                    ];
                }
            }else{
                $result = [
                    "status" => "charge_paid",
                    "message" => "$username paid this charge"
                ];
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "validation error"
            ];
        }
        $this->view("json", $result);
    }

    public function callback(){
        $getParams = $_GET;
        $model = $this->model('payment');
        $userModel = $this->model('user');
        $result = [];
        $validate = Validator::validateElements($getParams, [
            "username",
            "year",
            "month",
            "success",
            "status",
            "orderId",
            "trackId" 
        ]);
        unset($getParams);
        if ($validate){
            $username = $_GET["username"];
            $password = $_GET["password"];
            $year = $_GET["year"];
            $month = $_GET["month"];
            $success = $_GET["success"];
            $orderId = $_GET["orderId"];
            $trackId = $_GET["trackId"];
            $userInformation = $userModel->getUserInformation($username, $password);
            if ($success == 1){
                $parameters = array(
                    "merchant" => ZIBAL_MERCHANT_KEY,//required
                    "trackId" => $trackId//required
                );
                $response = $model->postToZibal("verify", $parameters);
                if ($response->result == 100){
                   $this->payCharge($userInformation, $price, $year, $month);
                   echo "Charge paid";
                }else{
                    $data = $model->statusCodes($response->result);
                    $this->view("payment_error", $data);
                }
            }else{
                echo "Success? $success";
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "validation failed"
            ];
        }
    }

}

?>
