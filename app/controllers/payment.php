<?php 
define("ZIBAL_MERCHANT_KEY", "zibal");
class Payment extends Controller {

    public function simple_charge(){
        session_start();
        $getParams = $_POST;
        $validate = Validator::validateElements($getParams, [
            'username',
            'password',
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
            $sessionId = session_id();
            $callbackUrl = "http://192.168.42.62/mojtama-server-mvc/payment/callback/?sessionId=$sessionId";
            $_SESSION["username"] = $username;
            $_SESSION["password"] = $password;
            $_SESSION["year"] = $currentYear;
            $_SESSION["month"] = $currentMonth;
            $_SESSION["price"] = $currentPrice;
            $parameters = array(
                "merchant"=> ZIBAL_MERCHANT_KEY,//required
                "callbackUrl" => $callbackUrl,
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
                    "message" => "the charge was paid"
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

    public function custom_charge(){
        $result = [];
        $postParams = $_POST;
        $validate = Validator::validateElements($postParams, [
            "username",
            "password",
            "json"
        ]);
        unset ($postParams);
        if ($validate){
            session_start();
            $sessionId = session_id();
            $model = $this->model("payment");
            $userModel = $this->model("user");
            $username = $_POST["username"];
            $password = $_POST["password"];
            $json = $_POST["json"];
            $infoFileName = "chargePricesInfo.json";
            $infoFile = fopen($infoFileName, "r");
            $infoFileContent = fread($infoFile, filesize($infoFileName));
            fclose($infoFile);
            $userInfo = $userModel->getUserInformation($username, $password);
            if (!$userInfo){
                $result = [
                    "status" => "error",
                    "message" => "user or password isn't correct!"
                ];
                $this->view("json", $result);
                return;
            }
            $phoneNumber = $userInfo->phone;
            $price = $model->generatePriceByCustomChargeJson($userInfo, $json, $infoFileContent);
            $_SESSION["user_info"] = $userInfo;
            $_SESSION["price"] = $price;
            $_SESSION["jsonEnteredByUser"] = $json;
            $callbackUrl = "http://192.168.42.62/mojtama-server-mvc/payment/custom_callback/?username={$userInfo->username}&sessionId={$sessionId}";
            $parameters = array(
                "merchant"=> ZIBAL_MERCHANT_KEY,//required
                "callbackUrl" => $callbackUrl,
                "amount"=> $price,//required
                "orderId"=> time(),//optional
                "mobile"=> $phoneNumber//optional for mpg
            );
            if ($price === false){
                $result = [
                    "status" => "error",
                    "message" => "try changing your months"
                ];
            } else if ($price === 0){
                $result = [
                    "status" => "error",
                    "message" => "price shouldn't be 0 Rial"
                ];
            } else{
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
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "validation failed"
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
            "sessionId",
            "success",
            "status",
            "trackId",
            "orderId"
        ]);
        unset($getParams);
        if ($validate){
            $userSessionId = $_GET["sessionId"];
            session_id($userSessionId);
            session_start();
            $username = $_SESSION["username"];
            $password = $_SESSION["password"];
            $year = $_SESSION["year"];
            $month = $_SESSION["month"];
            $price = $_SESSION["price"];
            $status = $_GET["status"];
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
                   $model->payCharge($userInformation, $price, $year, $month);
                   $data = "شارژ پرداخت شد!\nکد پیگیری: $trackId";
                   $this->view("payment_success", $data);
                }else{
                    $title = explode("-", $model->statusCodes($status))[0];
                    $data = $model->statusCodes($status);
                    $this->view("payment_error", $title, $data . " / کد پیگیری: $trackId");
                }
            }else{
                $title = "شکست";
                $data = $model->statusCodes($status);
                $this->view("payment_error", $title, "وضعیت: " . $data);
            }
        }else{
            $result = [
                "status" => "error",
                "message" => "validation failed"
            ];
            $this->view("json", $result);
        }
    }

    public function custom_callback(){
        $getParams = $_GET;
        $validate = Validator::validateElements($getParams, [
            "username",
            "sessionId"
        ]);
        if ($validate){
            $model = $this->model("payment");
            $userSessionId = $_GET["sessionId"];
            session_id($userSessionId);
            session_start();
            $userInfo = $_SESSION["user_info"];
            $jsonEnteredByUser = $_SESSION["jsonEnteredByUser"];
            $price = $_SESSION["price"];
            $status = $_GET["status"];
            $success = $_GET["success"];
            $orderId = $_GET["orderId"];
            $trackId = $_GET["trackId"];
            $parameters = array(
                "merchant" => ZIBAL_MERCHANT_KEY,//required
                "trackId" => $trackId//required
            );
            $response = $model->postToZibal("verify", $parameters);
            if ($success == 1){
                if ($response->result == 100){
                    $monthsPaid = $model->payCustomCharge($userInfo, $jsonEnteredByUser);
                    $data = "شما مبلغ $price ریال پرداخت کردید!<br>ماه‌های $monthsPaid را پرداخت کردید.<br>کد پیگیری: $trackId";
                    $this->view("payment_success", $data);
                }else{
                    $translatedStatusCode = $model->statusCodes($status);
                    $title = explode("-", $translatedStatusCode)[0];
                    $data = $translatedStatusCode;
                    $this->view("payment_error", $title, $data);
                }
            }else{
                $translatedStatusCode = $model->statusCodes($status);
                $title = explode("-", $translatedStatusCode)[0];
                $data = $translatedStatusCode;
                $this->view("payment_error", $title, $data);
            }
        }
    }

    public function get_payment_history(){
        $model = $this->model("payment");
        $paymentHistory = $model->getPayHistory();
        $result = [];
        if ($paymentHistory !== false){
            $result = $paymentHistory;
        }else{
            $result = [
                "status" => "error",
                "message" => "nothing exists"
            ];
        }
        $this->view("json", $result);
    }

    public function test(){
        $out = jdate('Y-m-d'); 
        echo $out;
    }
}

?>
