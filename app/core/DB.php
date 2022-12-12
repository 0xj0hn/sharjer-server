<?php 


class DB {
    protected $mysql;
    protected $dbinformation;
    public function __construct(){ //WE HAVE TO CHANGE THIS SECTION LATER.
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
    public function query($sql, $types, ...$values){
        $query = $this->mysql->prepare($sql);
        $query->bind_param($types, ...$values);
        $query->execute();
        return $query; //the query type is mysqli_stmt that's why i wrote it like this.
    }


}




?>
