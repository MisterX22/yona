<?php
  $conflist=$_GET['conflist'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10">
    <title>Connected users</title>
    <style type="text/css">
       #users {
          font-size: 100%;
          height:100%;
          overflow-y:auto;
       }
    </style>

</head>

<body>
   <div id="questions">
   <div id="users">
     <strong><u>Connected Users :</u></strong><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected > 0";
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req))
      {
        echo $data['name']."<br>" ;
        $name3=$data['name'] ;
        $sql3 = "UPDATE ".$conflist." SET isconnected = isconnected - 1 WHERE name='$name3'";
        //$sql3 = "UPDATE ".$conflist." SET isconnected='1' WHERE name='$name3'";
        mysqli_query($db,$sql3) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error());
      }
      mysqli_close($db);
      ?>

     <br><br>
     <strong><u>Registered Users :</u></strong><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error())
;
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected <= 0 ";   
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req)) 
      { 
        echo $data['name']."<br>" ; 
      } 
      mysqli_close($db);
      ?>

   </div>
</body>

</html>
