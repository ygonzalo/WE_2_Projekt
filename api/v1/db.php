<?php
class DB {
    private $host = 'localhost';
    private $username = 'weuser';
    private $password = 'weilbaum';
    private $database = 'filmsapp';
    private $conn;

    function __construct() {
        $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die;
    }

    public function getSingleRecord($query) {
       $result = mysqli_query($this->conn, $query.' LIMIT 1');

        return mysqli_fetch_assoc($result);
    }

    public function insertIntoTable($object, $column_names, $table_name) {
        $arr = (array) $object;
        $keys = array_keys($arr);
        $columns = '';
        $values = '';

        //Fills columns and their respective values strings for the db query
        foreach($column_names as $key) {
            if(!in_array($key, $keys)) {
                $$key = '';
            } else{
                $$key = $arr[$key];
            }
            $columns = $columns.$key.',';
            $values = $values."'".$$key."',";
        }
        mysqli_query($this->conn,"INSERT INTO ".$table_name."(".trim($columns,',').") VALUES(".trim($values,',').")");

        return mysqli_insert_id($this->conn);
    }

    public function getSession(){
        if (!isset($_SESSION)) {
            session_start();
        }
        $sess = array();
        if(isset($_SESSION['userId']))
        {
            $sess["userId"] = $_SESSION['userId'];
            $sess["name"] = $_SESSION['name'];
            $sess["email"] = $_SESSION['email'];
        }
        else
        {
            $sess["userId"] = '';
            $sess["name"] = 'Guest';
            $sess["email"] = '';
        }
        return $sess;
    }

    public function destroySession(){
        if (!isset($_SESSION)) {
            session_start();
        }
        if(isset($_SESSION['userId']))
        {
            unset($_SESSION['userId']);
            unset($_SESSION['name']);
            unset($_SESSION['email']);
            $msg="Logged Out Successfully...";
        }
        else
        {
            $msg = "Not logged in...";
        }
        return $msg;
    }
}


?>
