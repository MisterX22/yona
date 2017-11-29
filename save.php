<?php
header ( "Access-Control-Allow-Origin: *" );
header ( "Access-Control-Allow-Methods: GET, POST, PUT, DELETE" );
header ( "Access-Control-Allow-Headers: X-Requested-With" );
header ( 'Content-Type: application/json; charset=utf-8' );

if(isset($_GET['langfrom']))
    $langfrom = $_GET['langfrom'] ;
else
    $langfrom = "" ;
if(isset($_GET['langto']))
    $langto = $_GET['langto'] ;
else
    $langto = "" ;

foreach(array('video', 'audio') as $type) {
    if (isset($_FILES["${type}-blob"])) {

        $fileName = $_POST["${type}-filename"];
        $uploadDirectory = "uploads/$fileName";

        if (!move_uploaded_file($_FILES["${type}-blob"]["tmp_name"], $uploadDirectory)) {
            echo("problem moving uploaded file");
        }

        exec('/home/ubuntu/.nvm/versions/node/v6.11.2/bin/node /home/ubuntu/Project-X/translate.js ' . $fileName . ' ' . $langfrom . ' ' . $langto . ' >> log.txt');

       echo "?play=" . $fileName ;

    }
}
?>
