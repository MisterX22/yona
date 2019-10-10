<?php
    include('includes/db.php');

    /**
     * The Controller class holds all the application backend logic.
     * It also handles the access to database
     * @author PH
     */
    class Controller {

        public $database;

        /**
         * Create a controller connected to database.
         * DB access parameters are taken from environment variables
         */
        public function __construct() {
            $this->database = new Database();
        }

        /**
         * @return the inner database object
         */
        public function database() {
            return $this->database;
        }

        /**
         * Escape a string according to the database standards
         * @param $str The string to be escaped
         * @return The escaped string
         */
        public function escape($str) {
            return $this->database->escape($str);
        }

        /**
         * @return The list of existing conferences
         */
        public function list_conferences() {
            return $this->database->query_arrays('show tables');
        }

        /**
         * Checks if a conference exists
         * @param $name The conference name to be checked
         * @return true if it exists, else return false
         */
        public function conference_exists($name) {
            return $this->database->table_exists($name);
        }

        /**
         * Creates a new conference
         * @param $name The name of conference to create
         */
        public function create_conference($name) {
            $sql = "CREATE TABLE $name ( 
                id INT NOT NULL AUTO_INCREMENT, 
                name VARCHAR(30),
                firstreg BOOLEAN,
                isconnected INT,
                rtcid VARCHAR(30),
                macAddr VARCHAR(30),
                hostname VARCHAR(255),
                waitformic BOOLEAN,
                question VARCHAR(255),
                questime TIME,
                questove BOOLEAN,
                votefor  VARCHAR(30),
                votefo1  VARCHAR(30),
                votefo2  VARCHAR(30),
                votenum INT,
                login DATETIME,
                logout DATETIME,
                PRIMARY KEY (id)
            )";   
            $this->database->query($sql);
            // creating table for images
            $imagetable=$name."_images" ;
            $sql2 = "CREATE TABLE $imagetable ( 
                id INT NOT NULL AUTO_INCREMENT, 
                name VARCHAR(30),
                path VARCHAR(255),
                macAddr VARCHAR(30),
                date DATETIME,
                data LONGBLOB NOT NULL,
                mime VARCHAR(50) NOT NULL,
                PRIMARY KEY (id)
                )";   
            $this->database->query($sql2);
            // creating directory for configuration
            $sql3 = "CREATE TABLE IF NOT EXISTS conferences (
                    name VARCHAR(30) NOT NULL,
                    session_open TINYINT(1) NOT NULL,
                    PRIMARY KEY (name)
                )";
            $this->database->query($sql3);
            $this->database->query("INSERT INTO conferences (name, session_open) VALUES ('$name', 0)");
        }

        /**
         * Checks if audio session is opened for conference
         * @param $conference The conference name
         * @return true if opened, else false
         */
        public function is_session_open($conference) {
            $res = $this->database->query_arrays("SELECT session_open FROM conferences WHERE name = '$conference'");
            return $res[0][0] > 0; // TODO : What if conf does not exist ???
        }

        /**
         * Change the audio session state for a conference
         * @param $conference The conference to configure
         * @param $state true to open session, else false
         */
        public function set_session($conference, $state) {
            $status = $state ? 1 : 0;
            $this->database->query("UPDATE conferences SET session_open=$status WHERE name = '$conference'");
        }

        /**
         * Reset a conference, deleting all it's data
         * @param $name The conference to reset
         */
        public function reset_conference($name) {
            $imagetable=$name."_images" ;
            $this->database->query("DELETE FROM $name");
            $this->database->query("DELETE FROM $imagetable");
            $this->database->query("UPDATE conferences SET session_open=0 WHERE name = '$conference'");
        }

        /**
         * Delete a conference
         * @param $name The conference to delete
         */
        public function delete_conference($name) {
            $imagetable=$name."_images" ;
            $this->database->query("DROP TABLE $name");
            $this->database->query("DROP TABLE $imagetable");
            $this->database->query("DELETE FROM conferences name = '$conference'");
        }

        /**
         * Count how many questions a user has posted in a conference
         * @param $conference The name of the conference
         * @param $macAddr The address of the user
         * @return The number of questions posted
         */
        public function count_questions($conference, $macAddr) {
            $sql = "SELECT COUNT(*) FROM ".$conference." WHERE macAddr = '$macAddr' AND question != ''";   
            return $this->database->query_arrays($sql)[0][0];
        }

        public function count_empty_questions($conference, $macAddr) {
            $sql = "SELECT COUNT(*) FROM ".$conference." WHERE macAddr = '$macAddr' AND question = ''";   
            return $this->database->query_arrays($sql)[0][0];
        }

        public function remaining_questions($conference, $macAddr) {
            return 3 - $this->count_questions($conference, $macAddr);
        }

        public function post_question($conference, $macAddr, $name, $thequestion) {
            if($this->remaining_questions($conference, $macAddr) <= 0) {
                return false;
            }
            $sql = '';
            if ( $this->count_empty_questions($conference, $macAddr) == 0 ) 
            {
                $sql = "INSERT INTO ".$conference."(name,question, votenum, questime, macAddr, questove) 
                                    VALUES('$name','$thequestion','0',curtime(),'$macAddr','0')" ; 
            }
            else
            {
                $sql = "UPDATE ".$conference." SET question='$thequestion', votenum = '0', questime=curtime(), questove = '0' WHERE macAddr='$macAddr' AND question=''";
            }
            $this->database->query($sql);
            return true;
        }

        public function register_user($conference, $name, $hostname, $macAddr) {
            $sql2 = "SELECT COUNT(*) FROM ".$conference." WHERE macAddr = '".$macAddr."'";
            $count = $this->database->query_arrays($sql2)[0][0];
            if ( $count == 0 ) 
            {
                $sql = "INSERT INTO ".$conference."(name, hostname, firstreg, isconnected, rtcid, macAddr,waitformic, question,login, logout) 
                                        VALUES('$name', '$hostname', '1', '2','0','$macAddr','0','',now(),now())" ; 
            }
            else
            {
                $sql = "UPDATE ".$conference." SET hostname = '$hostname', name = '$name' , isconnected = '2', login = now() WHERE macAddr='$macAddr'";   
            }
            $this->database->query($sql);
        }

        public function get_username_from_macAddr($conference, $macAddr) {
            $sql = "SELECT name FROM ".$conference." WHERE macAddr = '$macAddr'";
            try {
              foreach($this->database->query_assocs($sql) as $madata) {
                $name = $madata['name'] ;
              }
              return $name;
            } catch(Exception $e) {
              return null;
            }
        }

        public function update_user_status($conference, $macAddr, $disconnect) {
            $sql = '';
            if($disconnect)
                $sql = "UPDATE ".$conference." SET isconnected = 0 WHERE macAddr='$macAddr'";
            else
                $sql = "UPDATE ".$conference." SET isconnected = 10 WHERE macAddr='$macAddr'";
            $this->database->query($sql);
        }

        public function logout_user($conference, $macAddr) {
            $sql = "UPDATE ".$conference." SET isconnected = 0 , logout=now() WHERE macAddr='$macAddr'";   
            $this->database->query($sql);
        }

        public function get_connected_users($conference) {
            $sql = "SELECT name FROM ".$conference." WHERE isconnected > 0 AND firstreg = '1'";   
            return $this->database->query_assocs($sql);
        }

        public function get_registered_users($conference) {
            $sql = "SELECT name, hostname FROM ".$conference." WHERE isconnected <= 0 AND firstreg = '1'";   
            return $this->database->query_assocs($sql);
        }

        public function decrement_connected_users_status($conference) {
            $sql = "UPDATE ".$conference." SET isconnected = isconnected - 1 WHERE isconnected > 0 AND firstreg = '1'";
            $this->database->query($sql);
        }

        public function list_images($conference) {
            return $this->database->query_assocs("SELECT id, name, path FROM ".$conference."_images");
        }

        public function get_image($conference, $id) {
            $sql = "SELECT mime, data FROM ".$conference."_images WHERE id = '".$id."'";
            return $this->database->query_assocs($sql)[0]; // TODO: What if image does not exist ???
        }

        public function save_image($conference, $name, $macAddr, $path, $mime, $data) {
            $imagetable = $conference."_images" ;
            $sql = "INSERT INTO ".$imagetable."(name,path,macAddr,date,mime,data) 
                                   VALUES('$name', '$path', '$macAddr',now(),'$mime',\"".$this->escape($data)."\")" ;
            $this->database->query($sql);
        }
    }
?>
