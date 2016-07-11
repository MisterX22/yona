<?php 
  $conflist=$_GET['conflist'];

  $ipclient=$_SERVER['REMOTE_ADDR'];
  $macAddr=false;
  $arp=`arp -a $ipclient`;
  $lines=explode(" ", $arp);
  $macAddr=$lines[3];


if(isset($_GET['votefor']))
{
    $votefor=$_GET['votefor'];
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $sql = "INSERT INTO ".$conflist."(name,votefor)
                                     VALUES('$name','$votefor')
                                     ON DUPLICATE KEY UPDATE name='$name', votefor='$votefor'";

    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db);
}

if(isset($_GET['name']))
{
    $name=$_GET['name'];
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $sql = "UPDATE ".$conflist." SET isconnected='1' WHERE macAddr='$macAddr'";
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db);
}
else
{
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $sql = "UPDATE ".$conflist." SET isconnected='0' WHERE macAddr='$macAddr'";
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db);
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
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT votefor FROM ".$conflist." WHERE macAddr = '$macAddr'";   
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req)) 
      { 
        $myvote = $data['votefor'] ;
      } 
      mysqli_close($db);
   ?>

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
        if ( $myvote == $macAddr2 ) {
           echo "<strong>";
        }
        echo "<a href='connected.php?conflist=".$conflist."&votefor=".$macAddr2."&name=".$name."'>".$data['question']."(".$macAddr2 ; 
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE votefor = '".$macAddr2."'";
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row = mysqli_fetch_array($req2);
        $count = $row[0];
        echo ",".$count." votes)</a><br>";
        $sql3 = "INSERT INTO ".$conflist."(votenum)
                                     VALUES('$count')
                                     ON DUPLICATE KEY UPDATE votenum='$count'";
        mysqli_query($db,$sql3) or die('Erreur SQL !'.$sql3.'<br>'.mysqli_error($db));
        if ( $myvote == $macAddr2 ) {
           echo "</strong>";
        }
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
      } 
      mysqli_close($db);
      ?>
   </div>
</body>

</html>
