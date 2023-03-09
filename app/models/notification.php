<?php
require_once "vendor/autoload.php";
class NotificationModel extends Model {
    public function sendNotif($title='title', $body='body', $devicesTokens){
        $curl = curl_init();
        $parameters = [
            "registration_ids" => $devicesTokens,
            "notification" => [
                "title" => $title,
                "body" => $body
            ],
        ];
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: key=AAAATYXhtFo:APA91bEoQuu4P6FO0Nd9qaQ_LzbZv9NLrhfp9QFNfX30kT6PugCuMhUw0wPSLFOu4W7netdx6rCy5FYP75AZ-EpUZQ3PA4oQwy-MGyH_p-Y_OX8QlwE_aeGw8Q_omlilYEPcXCWNFEsF"
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
            echo $response;
            return $response;
        }
    }

    public function sendNotifToAllMembersOnChargeTime(){ //this function should be checked.
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
        $title = "پرداخت شارژ";
        $body = "نکنه شارژ رو یادت بره این ماه!\nسریع پرداخت کن!";
        $sendNotifs = $this->sendNotif($title, $body, $usersFirebaseTokens);
        return $sendNotifs;
    }

    public function updateFirebaseToken($username, $givenToken){
        $sql = "UPDATE users SET firebase_token = ? WHERE username = ?";
        $query = $this->query($sql, "ss", $givenToken, $username);
        return $query;
    }
}
