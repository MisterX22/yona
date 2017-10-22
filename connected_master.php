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
          echo $data['name']." (".$data['hostname'].")<br>" ;
        }
        $controller->decrement_connected_users_status($conflist);
     ?>

     <br><br>
     <strong><u>Registered Users :</u></strong><br>
     <?php 
      foreach($controller->get_registered_users($conflist) as $data) { 
        echo $data['name']." (".$data['hostname'].")<br>" ; 
      } 
     ?>

   </div>
</body>

</html>
