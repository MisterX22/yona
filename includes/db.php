<?php

/**
 * A class handling database access
 * @author PH
 */
class Database {
    public $db;

    /**
     * Initialize a database link from environment variables
     * @throws Exception if connection or DB selection fails
     */
    public function __construct() {
        $this->db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
        if(!$this->db) {
            throw new Exception('Erreur de connexion '.mysqli_connect_error());
        }
        if(!mysqli_select_db($this->db, getenv('MYSQL_DB'))) {
            throw new Exception('Erreur de selection '.mysqli_error($this->db));
        }
    }

    /**
     * Destructor, releasing database resources
     */
    public function __destruct() {
        mysqli_close($this->db);
    }

    /**
     * Escape a string according to the database standards
     * @param $str The string to be escaped
     * @return The escaped string
     */
    public function escape($str) {
        return mysqli_real_escape_string($this->db, $str);
    }

    /**
     * Verify if a table exists
     * @param $name The table to look for
     * @return true if the table exists, else false
     */
    public function table_exists($name) {
        return mysqli_num_rows($this->query("SHOW TABLES LIKE '$name'")) >= 1;
    }

    /**
     * Execute a SQL query
     * @param $stmt The statement to run
     * @return SQL result
     * @throws Exception If query fails to execute
     */
    public function query($stmt) {
        $req = mysqli_query($this->db, $stmt);
        if(!$req) {
            throw new Exception('Erreur SQL !'.$stmt.'<br>'.mysqli_error($this->db));
        }
        return $req;
    }

    /**
     * Excecute a SQL query and returns result in an array of numeric indexed arrays
     * @param $stmt The SQL statment to run
     * @return The result as an array of numeric indexed arrays
     * @throws Exception if the query fails to execute
     */
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

    /**
     * Excecute a SQL query and returns result in an array of associative arrays
     * @param $stmt The SQL statment to run
     * @return The result as an array of associative arrays
     * @throws Exception if the query fails to execute
     */
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