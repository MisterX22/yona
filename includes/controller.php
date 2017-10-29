<?php
    include('includes/db.php');

    class Controller {

        public $database;

        public function __construct() {
            $this->database = new Database();
        }

        public function database() {
            return $this->database;
        }

        public function escape($str) {
            return $this->database->escape($str);
        }

        public function list_conferences() {
            return $this->database->query_arrays('show tables');
        }

        public function conference_exists($name) {
            return $this->database->table_exists($name);
        }

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
            // creating directory for multimedia
            $path="upload/".$name."/" ;
            mkdir($path , 0755, true) ;
            $imagetable=$name."_images" ;
            $sql2 = "CREATE TABLE $imagetable ( 
                id INT NOT NULL AUTO_INCREMENT, 
                name VARCHAR(30),
                path VARCHAR(255),
                macAddr VARCHAR(30),
                date DATETIME,
                PRIMARY KEY (id)
                )";   
            $this->database->query($sql2);
            // creating directory for configuration
            $path="configuration/".$name."/" ;
            mkdir($path , 0755, true) ;
            $sessionopen="No" ;
            $file = $path."configuration.txt" ;
            file_put_contents($file, $sessionopen);
            // creating directory for trash
            $path="trash/".$name."/" ;
            mkdir($path , 0755, true) ;
        }

        public function reset_conference($name) {
            $imagetable=$name."_images" ;
            $this->database->query("DELETE FROM `$name`");
            $this->database->query("DELETE FROM `$imagetable`");
        }

        public function delete_conference($name) {
            $imagetable=$name."_images" ;
            $database->query("DROP TABLE `$name`");
            $database->query("DROP TABLE `$imagetable`");
            $path="upload/".$name."/" ;
            $trash="trash/";
            rename($path,$trash) ;
        }

        public function list_images($conference) {
            return $this->database->query_assocs("SELECT name, path FROM ".$conference."_images");
        }

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

        public function post_question($conference, $macAddr, $thequestion) {
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
                                        VALUES('$name', '$hostname', '1', '2','','$macAddr','','',now(),'')" ; 
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
            $sql = "SELECT name FROM ".$conference." WHERE isconnected <= 0 AND firstreg = '1'";   
            return $this->database->query_assocs($sql);
        }

        public function decrement_connected_users_status($conference) {
            $sql = "UPDATE ".$conference." SET isconnected = isconnected - 1 WHERE isconnected > 0 AND firstreg = '1'";
            $this->database->query($sql);
        }

        public function save_image_path($conference, $name, $macAddr, $path) {
            $imagetable = $conference."_images" ;
            $sql = "INSERT INTO ".$imagetable."(name,path,macAddr,date) 
                                   VALUES('$name', '$path', '$macAddr',now())" ; 
            $this->database->query($sql);
        }
    }
?>