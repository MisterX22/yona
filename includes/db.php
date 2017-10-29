<?php
class Database {
    public $db;

    public function __construct() {
        $this->db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
        if(!$this->db) {
            throw new Exception('Erreur de connexion '.mysqli_connect_error());
        }
        if(!mysqli_select_db($this->db, getenv('MYSQL_DB'))) {
            throw new Exception('Erreur de selection '.mysqli_error($this->db));
        }
    }

    public function __destruct() {
        mysqli_close($this->db);
    }

    public function escape($str) {
        return mysqli_real_escape_string($this->db, $str);
    }

    public function table_exists($name) {
        return mysqli_num_rows($this->query("SHOW TABLES LIKE '$name'")) >= 1;
    }

    public function query($stmt) {
        $req = mysqli_query($this->db, $stmt);
        if(!$req) {
            throw new Exception('Erreur SQL !'.$stmt.'<br>'.mysqli_error($this->db));
        }
        return $req;
    }

    public function query_arrays($stmt) {
        $res = $this->query($stmt);
        $array = [];
        //TODO: Maybe use mysqli_fetch_all($res) instead
        while($row = mysqli_fetch_array($res)) {
            $array[] = $row;
        }
        mysqli_free_result($res);        
        return $array;
    }

    public function query_assocs($stmt) {
        $res = $this->query($stmt);
        $array = [];
        //TODO: Maybe use mysqli_fetch_all($res) instead
        while($row = mysqli_fetch_assoc($res)) {
            $array[] = $row;
        }
        mysqli_free_result($res);        
        return $array;
    }
}
?>