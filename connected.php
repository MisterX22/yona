<?php 

// Retrieving required inputs
$conflist=$_GET['conflist'];

$ipclient=$_SERVER['HTTP_X_FORWARDED_FOR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
//$macAddr=$lines[3];
$macAddr=$ipclient;

$load= sys_getloadavg() ;
if ( $load[0] > 60 )
  $refreshTime=120 ;
else
  $refreshTime=60 ;

// Updating connection status list
if(isset($_GET['action']))
  {
    $action=$_GET['action'];
    //if(isset($_GET['name']))  $name=$_GET['name'];
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
      //if(isset($_GET['name']))
      //{
        //$name=$_GET['name'];
        $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
        mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
        $sql = "UPDATE ".$conflist." SET isconnected = 10 WHERE macAddr='$macAddr'";
        mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
        mysqli_close($db);
      //}
  }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php
      echo "<meta http-equiv='refresh' content='".$refreshTime."' URL=\"https://yona-misterx22.c9users.io/connected.php?conflist=".$conflist."&action='C'\" />" ;
    ?>
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
   <div id="users">

     <form name="refresh" id="refresh" method="post" action="https://yona-misterx22.c9users.io/connected.php?conflist=<?php if (isset($conflist)) echo $conflist?>&action='C'">
       <table><tr>
       <td style="text-align: center;">Auto page refresh <?php echo $refreshTime ; ?> seconds</td>
       <td><input type='button' value='Manual Refresh' onclick='this.form.submit()'></td>
       </tr></table>
     </form><br>

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
