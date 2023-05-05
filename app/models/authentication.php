<?php 

class AuthenticationModel extends Model{
    //we store the POST data into data field
    protected $data;
    public function encryptValue($value){
        $encryptedString = $this->encrypt($value);
        return $encryptedString;
    }

    public function decryptValue($value){
        $decryptedString = $this->decrypt($value);
        return $decryptedString;
    }
    public function login($username, $password){
        $username = trim($username);
        $sql = "SELECT * FROM users WHERE `username` = ? AND `password` = ?";
        $query = $this->query($sql, "ss", $username, $password);
        $result = $query->get_result();
        if ($result->num_rows > 0){
            return true;
        }else{
            return false;
        }
    }

    public function signUp($data){
        $isCheckRequiredElements = Validator::validateElements($data, [
            'username',
            'password',
            'name',
            'family',
            'phone',
            'phone2',
            'bluck',
            'vahed',
            'family_members',
            'car_plate',
            'start_date',
            'is_owner',
        ]);
        if ($isCheckRequiredElements != true) return false; 
        $isValidatedNeededElements = $this->validatePhoneUserBluckVahed($data);
        $checkEndDate = (isset($data['end_date']) && !empty($data['end_date']));
        $enddate = $checkEndDate ? $data['end_date'] : "";
        $userExists = $this->checkIfUserExists($data['username']);
        if ($isValidatedNeededElements && !$userExists){
            $password = $data['password'];
            $sql = "INSERT INTO users (username, password, name, family, phone, phone2, bluck, vahed, startdate, enddate, is_owner, car_plate, family_members) VALUES 
(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $query = $this->query($sql,
                "ssssssiissisi",
                $data['username'],
                $password,
                $data['name'],
                $data['family'],
                $data['phone'],
                $data['phone2'],
                $data['bluck'],
                $data['vahed'],
                $data['start_date'],
                $enddate,
                $data['is_owner'],
                $data['car_plate'],
                $data['family_members'],
            );
            return $query ? true : false;
        }else{
            if ($userExists) return -1;
            return false;
        }
    }



    public function validatePhoneUserBluckVahed($data){
        $validateUsername = Validator::validateUsername($data["username"]); 
        $validatePhone1 = Validator::validatePhone($data["phone"]); 
        $validatePhone2 = Validator::validatePhone($data["phone2"]); 
        $validateBluck = Validator::validateBluckOrVahed($data["bluck"]); 
        $validateVahed = Validator::validateBluckOrVahed($data["vahed"]);
        if ($validateUsername &&
            $validatePhone1 &&
            $validatePhone2 &&
            $validateBluck &&
            $validateVahed
        ){
            return true;
        }else{
            return false;
        }
    }

    public function checkIfUserExists($username){
        $sql = "SELECT * FROM users WHERE username = ?";
        $query = $this->query($sql, "s", $username);
        $result = $query->get_result();
        if ($result->num_rows == 0) return false;
        return true;
    }
}
?>
