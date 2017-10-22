<?php 
include('includes/controller.php');
$controller = new Controller();

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
    $controller->update_user_status($conflist, $macAddr, $action == "D");
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php
      echo "<meta http-equiv='refresh' content='".$refreshTime."' URL=\"./connected.php?conflist=".$conflist."&action='C'\" />" ;
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

     <form name="refresh" id="refresh" method="post" action="./connected.php?conflist=<?php if (isset($conflist)) echo $conflist?>&action='C'">
       <table><tr>
       <td style="text-align: center;">Auto page refresh <?php echo $refreshTime ; ?> seconds</td>
       <td><input type='button' value='Manual Refresh' onclick='this.form.submit()'></td>
       </tr></table>
     </form><br>

     <strong><u>Connected Users :</u></strong><br>
     <?php
      foreach($controller->get_connected_users($conflist) as $data) { 
        echo $data['name']."<br>" ; 
      } 
      ?>

     <br><br>
     <strong><u>Registered Users :</u></strong><br>
     <?php 
      foreach($controller->get_registered_users($conflist) as $data) { 
        echo $data['name']."<br>" ; 
      } 
      ?>

   </div>
</body>

</html>
