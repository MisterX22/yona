<?php 
  $conflist=$_GET['conflist'];
  $name=$_GET['name'];

if(isset($_GET['votefor']))
{
    $votefor=$_GET['votefor'];
    $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
    mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
    $sql = "INSERT INTO ".$conflist."(name,votefor)
                                     VALUES('$name','$votefor')
                                     ON DUPLICATE KEY UPDATE name='$name', votefor='$votefor'";

    mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
    mysql_close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10">
    <title>Connected users</title>
    <style type="text/css">
       #questions {
          height:200px;
          overflow-y:auto;
       }
       #users {
          height:100px;
          overflow-y:auto;
       }
    </style>

</head>

<body>

   <?php
      $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
      mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
      $sql = "SELECT votefor FROM ".$conflist." WHERE name = '$name'";   
      $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
      while($data = mysql_fetch_assoc($req)) 
      { 
        $myvote = $data['votefor'] ;
      } 
      mysql_close();
   ?>

   <div id="questions">
     <strong><u> Questions :</u></strong><br>
     <?php
      $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
      mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
      $sql = "SELECT question , name FROM ".$conflist." WHERE question !='' ORDER BY votenum DESC";   
      $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
      while($data = mysql_fetch_assoc($req)) 
      { 
        $name2 = $data['name'];
        if ( $myvote == $name2 ) {
           echo "<strong>";
        }
        echo "<a href='connected.php?conflist=".$conflist."&votefor=".$name2."&name=".$name."'>".$data['question']."(".$name2 ; 
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE votefor = '".$name2."'";
        $req2 = mysql_query($sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysql_error());
        $row = mysql_fetch_array($req2);
        $count = $row[0];
        echo ",".$count." votes)</a><br>";
        $sql3 = "INSERT INTO ".$conflist."(name,votenum)
                                     VALUES('$name2','$count')
                                     ON DUPLICATE KEY UPDATE name='$name2', votenum='$count'";
        mysql_query($sql3) or die('Erreur SQL !'.$sql3.'<br>'.mysql_error());
        if ( $myvote == $name2 ) {
           echo "</strong>";
        }
      } 
      mysql_close();
      ?>
   </div>
   <div id="users">
     <strong><u>Connected Users :</u></strong><br>
     <?php
      $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
      mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected ='1'";   
      $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
      while($data = mysql_fetch_assoc($req)) 
      { 
        echo $data['name'].", " ; 
      } 
      mysql_close();
      ?>
   </div>
</body>

</html>
