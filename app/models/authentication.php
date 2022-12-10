<?php 

class AuthenticationModel extends Model{
    protected $data;
    public function login($username, $password){
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
        $this->data = $data;
        $isCheckRequiredElements = Validator::validateElements($this->data, [
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
        $isValidatedNeededElements = $this->validatePhoneUserBluckVahed();
        $checkEndDate = (isset($data['end_date']) && !empty($data['end_date']));
        echo $checkEndDate;
        $enddate = $checkEndDate ? $data['end_date'] : ""; //TODO: implement it
        if ($isValidatedNeededElements){
            $password = md5($data['password']);
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
            return false;
        }
    }

    public function validatePhoneUserBluckVahed(){
        $validateUsername = Validator::validateUsername($this->data["username"]); // true
        $validatePhone1 = Validator::validatePhone($this->data["phone"]); //true
        $validatePhone2 = Validator::validatePhone($this->data["phone2"]); //true
        $validateBluck = Validator::validateBluckOrVahed($this->data["bluck"]); // false
        $validateVahed = Validator::validateBluckOrVahed($this->data["vahed"]); //true
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
}
?>
