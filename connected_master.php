<?php
  include('includes/controller.php');
  $controller = new Controller();

  $conflist=$_GET['conflist'];
  $load[0]=0;
  if (strncasecmp(PHP_OS, 'WIN', 3) != 0) {
    $load= sys_getloadavg() ;
  }
  if ( $load[0] > 60 )
    $refreshTime=20 ;
  else
    $refreshTime=10 ;

  if (isset($_POST['erase']))
  {
    exec('rm uploads/*.* ; rm translated/*.*;');
  }
  if (isset($_POST['clean']))
  {
    exec('rm translated/*.*') ;
  }
  $langfrom = "en-US";
  $langto = "fr-FR" ;
  if (isset($_POST['lang-from']))
  {
    $langfrom = $_POST['lang-from'] ;
  }
  if (isset($_POST['lang-to']))
  {
    $langto   = $_POST['lang-to']   ; 
  }
  if (isset($_POST['button']))
  {
    $file=$_POST['button'] ;
    exec('/home/ubuntu/.nvm/versions/node/v6.11.2/bin/node /home/ubuntu/NodeJs-Example/translate.js ' . $file . ' ' . $langfrom . ' ' . $langto . ' > log.txt');
  }
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="<?php echo $refreshTime ;?>">
    <title>Connected users</title>
    <style type="text/css">
       #users {
          font-size: 100%;
          height:100%;
          overflow-y:auto;
       }
    </style>

</head>

<body style="font-family: 'Arial';">
   <div id="questions">
   Server Load : <?php echo $load[0] ;?>% / Refresh Time : <?php echo $refreshTime ;?><br><br>
   <div id="users">
     <strong><u>Connected Users :</u></strong><br>
     <?php
      foreach($controller->get_connected_users($conflist) as $data) {
          //echo $data['name']." (".$data['hostname'].")<br>" ;
          echo $data['name']."<br>" ;
        }
        $controller->decrement_connected_users_status($conflist);
     ?>

     <br><br>
     <strong><u>Registered Users :</u></strong><br>
     <?php 
      foreach($controller->get_registered_users($conflist) as $data) { 
        //echo $data['name']." (".$data['hostname'].")<br>" ; 
        echo $data['name']."<br>" ;
      } 
     ?>

     <br><br>
     <strong><u>Records</u></strong>
     <form method="post">
       <button name="erase">Erase All</button>
       <button name="clean">Clean All</button>
       From :
       <select name="lang-from" onchange="this.form.submit()">
          <option value="en-US" <?php if($langfrom == "en-US") echo 'selected= "selected"';?>>en-US</option>
          <option value="fr-FR" <?php if($langfrom == "fr-FR") echo 'selected= "selected"';?>>fr-FR</option>
          <option value="de-DE" <?php if($langfrom == "de-DE") echo 'selected= "selected"';?>>de-DE</option>
        </select>
       To :
       <select name="lang-to" onchange="this.form.submit()">
         <option value="en-US" <?php if($langto == "en-US") echo 'selected= "selected"';?>>en-US</option>
         <option value="fr-FR" <?php if($langto == "fr-FR") echo 'selected= "selected"';?>>fr-FR</option>
         <option value="de-DE" <?php if($langto == "de-DE") echo 'selected= "selected"';?>>de-DE</option>
       </select>
     <!--</form>
     <form method="post">--!>
     <?php
       $dir = "uploads";
       $dirtranslated = "translated/";
       $exclude = array( ".","..","error_log","_notes" ); 
       if (is_dir($dir)) {
         $files = scandir($dir);
         echo "<table border='1px' width='800px'>";
         echo "<tr><th>Action</th><th>Original Stream</th><th>Traduction Stream</th><th>Recognition text</th><th>Traduction Text</th></tr>"; 
         foreach($files as $file){
           if(!in_array($file,$exclude)){
             $translation=$dirtranslated.$file ;
             if (file_exists($translation)) {
               $buttonstate="disabled" ;
             }
             else $buttonstate="" ;
             echo "<tr>" ;
             echo "<td><button name='button' value='" . $file . "'" . $buttonstate . ">Translate</button>" ;
             echo "<td><a href='" .  $dir . "/" . $file . "'>" . $file . "</a></td>";
             if (file_exists($translation)) {
               echo "<td style='text-align: center;'><a href='" . $translation ."'>" . "&#9658;</a></td>";
               $translationtxt=$translation.".txt";
               $recognitiontxt=$translation.".recog";
               $contenurecog=file_get_contents($recognitiontxt); 
               $contenutrans=file_get_contents($translationtxt); 
               echo "<td>" . $contenurecog . "</td>" ;
               echo "<td>" . $contenutrans . "</td>" ;
             }
             else echo "<td></td><td></td><td></td>";
             echo "</tr>" ;
           }
         }
         echo "</table>";
       }
     ?>
     </form>

   </div>
</body>

</html>
