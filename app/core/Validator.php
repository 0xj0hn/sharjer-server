<?php 

class Validator {

    public static function validateElements($data, $keyList){
        $checkedValues = [];
        if (empty($data)) return false;
        foreach($keyList as $key){
            if (array_key_exists($key, $data)){
                $element = $data["$key"];
                if(isset($element)){
                    array_push($checkedValues, $element);
                }
            }
        }
        if (count($checkedValues) == count($keyList)){
            return true;
        }else{
            return false;
        }
    }

    public static function validateUsername($username){
        $latin_match_regex = "/([a-z])\\w+/";
        if (preg_match($latin_match_regex, $username)){
            return true;
        }else{
            return false;
        }
    }

    public static function validatePhone($phoneNumber){
        if (strlen($phoneNumber) == 11 || $phoneNumber[0] == "0"){
            return true;
        }else{
            return false;
        }
    }

    public static function validateBluckOrVahed($input){
        if (is_numeric($input)){
            return true;
        }else{
            return false;
        }
    }

}


?>
