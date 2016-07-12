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
   <div id="questions">
     <strong><u> Questions :</u></strong><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT question , name, macAddr FROM ".$conflist." WHERE question !='' ORDER BY votenum DESC";
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req))
      {
        $macAddr2 = $data['macAddr'];
        $name2 = $data['name'];
        echo $data['question']."(".$name2 ;
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE votefor = '".$macAddr2."'";
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row = mysqli_fetch_array($req2);
        $count = $row[0];
        echo ",".$count." votes)<br>";
      }
      mysqli_close($db);
     ?>
   </div>
   <div id="users">
     <strong><u>Connected Users :</u></strong><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected ='1'";
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req))
      {
        echo $data['name'].", " ;
        $name3=$data['name'] ;
        $sql3 = "UPDATE ".$conflist." SET isconnected='0' WHERE name='$name3'";
        mysqli_query($db,$sql3) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error());
      }
      mysqli_close($db);
      ?>
   </div>
</body>

</html>
