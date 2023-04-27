<?php
class NotificationModel extends Model {
    public function sendNotif($title='title', $body='body', $devicesTokens, $navigation=null){
        $devicesTokens = array_unique($devicesTokens);
        $curl = curl_init();
        $parameters = [
            "registration_ids" => $devicesTokens,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
        ];
        if ($navigation != null){
            $parameters["data"] = [
                "navigation" => $navigation
            ];
        }
        $firebaseTokenFileName = "firebase_token";
        $firebaseApiKey = file_get_contents($firebaseTokenFileName);
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: key=$firebaseApiKey"
            ],
            CURLOPT_POSTFIELDS => json_encode($parameters)
        ]
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err){
            echo $err;
            return false;
        }else{
            return $response;
        }
    }

    public function sendNotifToAllMembers($title, $body){ //this function is not in use yet.
        $usersFirebaseTokens = $this->getAllUsersTokens();
        if ($usersFirebaseTokens){
            $sendNotifs = $this->sendNotif($title, $body, $usersFirebaseTokens);
            return $sendNotifs;
        }
        return false;
    }

    public function getAllUsersTokens(){
        $sql = "SELECT * FROM users";
        $query = $this->query($sql);
        $result = $query->get_result();
        $usersFirebaseTokens = [];
        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $firebaseToken = $row["firebase_token"];
                if (!empty($firebaseToken)){
                    $usersFirebaseTokens[] = $row["firebase_token"];
                }
            }
        }else{
            return false;
        }
        if (count($usersFirebaseTokens) == 0){
            return false;
        }
        return $usersFirebaseTokens;
    }

    public function sendNotifToAllAdmins($chargePayerFullName, $year, $month, $price){
        $title = "واریزی";
        $body = "شارژ ماه $month برای سال $year همین الان توسط $chargePayerFullName پرداخت شد!!";
        $adminDevicesTokens = $this->getAdminsTokens();
        if ($adminDevicesTokens){
            $navigationPage = "/history_screen";
            $sendNotifs = $this->sendNotif($title, $body, $adminDevicesTokens, $navigationPage);
            return $sendNotifs;
        }
        return false;
    }

    public function getAdminsTokens(){
        $sql = "SELECT * FROM users WHERE is_admin <> ?";
        $query = $this->query($sql, "s", "no");
        $result = $query->get_result();
        $adminDevicesTokens = [];
        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $adminDeviceToken = $row["firebase_token"];
                if (!empty($adminDeviceToken)){
                    $adminDevicesTokens[] = $adminDeviceToken;
                }
            }
        }else{
            return false;
        }
        if (count($adminDevicesTokens) == 0){
            return false;
        }
        return $adminDevicesTokens;
    }

    public function updateFirebaseToken($username, $givenToken){
        $sql = "UPDATE users SET firebase_token = ? WHERE username = ?";
        $query = $this->query($sql, "ss", $givenToken, $username);
        return $query;
    }
}
