<?php
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

        //exec('/home/ubuntu/.nvm/versions/node/v6.11.2/bin/node /home/ubuntu/NodeJs-Example/translate.js ' . $fileName . ' ' . 'fr-FR' . ' ' . 'en-US' . ' > log.txt');
        exec('/home/ubuntu/.nvm/versions/node/v6.11.2/bin/node /home/ubuntu/NodeJs-Example/translate.js ' . $fileName . ' ' . $langfrom . ' ' . $langto . ' > log.txt');

       echo "?play=" . $fileName ;

    }
}
?>
