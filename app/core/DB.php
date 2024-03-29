<?php 


class DB {
    protected $mysql;
    protected $dbinformation;
    private $encryptionMethod = "AES-256-CBC";
    private $encryptionKey = "mojtama-amoli-residentialcomplex"; //32 bytes
    private $encryptionIV = "mojtama-amoliaaa"; //16 bytes
    public function __construct(){
        $host = "localhost";
        $username = "root";
        $password = "Mhdmhdmhd82@#";
        $dbname = "amoli";
        $this->dbinformation = (object)[
            "host" => $host,
            "username" => $username,
            "password" => $password,
            "dbname" => $dbname,
            "blucks" => [
                1,2,3
            ]
        ];

        $this->mysql = new mysqli($host, $username, $password, $dbname);
        if ($this->mysql->connect_error){
            die("ERROR: " . $this->mysql->connect_error);
        }
    }

    /*
     * @param $sql string the sql command to execute.
     * @param $types string the variable types of the command to execute. like "ssi" (string, string, int)
     * @param $values array() an array of values belongs to sql command.
     * @return return the response of query execution.
     */
    public function query($sql, $types=null, ...$values){
        $query = $this->mysql->prepare($sql);
        //bind parameters only when $types is not null.
        if ($types != null){
            $query->bind_param($types, ...$values ?? null);
        }
        $query->execute();
        return $query;
    }

    protected function encrypt($plainText){
        $cipherText = openssl_encrypt(
            $plainText,
            $this->encryptionMethod,
            $this->encryptionKey,
            0,
            $this->encryptionIV
        );
        return $cipherText;
    }

    protected function decrypt($cipherText){
        $plainText = openssl_decrypt(
            $cipherText,
            $this->encryptionMethod,
            $this->encryptionKey,
            0,
            $this->encryptionIV
        );
        return $plainText;
    }


}




?>
