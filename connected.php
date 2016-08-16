<?php 

// Retrieving required inputs
$conflist=$_GET['conflist'];

$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
$macAddr=$lines[3];

// Updating connection status list
if(isset($_GET['action']))
  {
    $action=$_GET['action'];
    if(isset($_GET['name']))  $name=$_GET['name'];
    if ( $action == "D")
      {
        $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
        mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
        $sql = "UPDATE ".$conflist." SET isconnected = 0 WHERE macAddr='$macAddr'";
        mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
        mysqli_close($db);
      }
    else
    {
      if(isset($_GET['name']))
      {
        $name=$_GET['name'];
        $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
        mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
        $sql = "UPDATE ".$conflist." SET isconnected = 2 WHERE macAddr='$macAddr'";
        mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
        mysqli_close($db);
      }
    //else
    //  {
    //    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    //    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    //    $sql = "UPDATE ".$conflist." SET isconnected = (isconnected - 1) WHERE macAddr='$macAddr'";
    //    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    //    mysqli_close($db);
    //  }
  }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10" URL="connected.php" />
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
   <div id="users">
     <strong><u>Connected Users :</u></strong><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected > 0 AND firstreg = '1'";   
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req)) 
      { 
        echo $data['name']."<br>" ; 
      } 
      mysqli_close($db);
      ?>

     <br><br>
     <strong><u>Registered Users :</u></strong><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE isconnected <= 0 AND firstreg = '1'";   
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
