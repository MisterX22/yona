<?php 
  $conflist=$_GET['conflist'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10">
    <title>Connected users</title>
</head>

<body>
   <strong><u>Connected Users :</u></strong><br>
   <?php
      $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
      mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected ='1'";   
      $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
      while($data = mysql_fetch_assoc($req)) 
      { 
        echo $data['name']."<br>" ; 
      } 
      mysql_close();
    ?>
   <strong><u> Questions :</u></strong><br>
   <?php
      $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
      mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
      $sql = "SELECT question , name FROM ".$conflist." WHERE question !=''";   
      $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
      while($data = mysql_fetch_assoc($req)) 
      { 
        echo $data['question']."(".$data['name'].") <br>" ; 
      } 
      mysql_close();
    ?>


</body>

</html>
