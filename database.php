<?php
  include('includes/controller.php');
  $controller = new Controller();
  $database = $controller->database();

  $conflist=$_GET['conflist'];

  $load= sys_getloadavg() ;
  if ( $load[0] > 60 )
    $refreshTime=120 ;
  else
    $refreshTime=60 ;
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="<?php echo $refreshTime ;?>">
    <title>Database</title>
    <style type="text/css">
       #databasePrint  {
          font-size: 100%;
          height:100%;
          overflow-y:auto;
       }
    </style>

</head>

<body style="font-family: 'Arial';">
   <div id="databasePrint">
     <br>
     <form name="refresh" id="refresh" method="post" action="./database.php?conflist=<?php if (isset($conflist)) echo 
$conflist?>">
       <table><tr><td><input type='button' value='Refresh' onclick='this.form.submit()'></td></tr></table>
     </form>

     <?php
      if ( isset($conflist) && ($conflist != "") ) 
      {
        //TODO: Discover fields dynamically
        $fields = [ 'id', 'name', 'macAddr', 'isconnected', 'firstreg', 'rtcid',
                    'waitformic', 'question', 'questime',
                    'questove', 'votefor', 'votefo1', 'votefo2', 'votenum', 'login',
                    'logout', 'hostname'];
        echo "<br><table border='2px'>" ;
        echo "<tr>" ;
        foreach($fields as $field) {
          echo "<th>$field</th>";
        }
        echo "</tr>" ;
        foreach($database->query_assocs("SELECT * FROM ".$conflist) as $data)
        {
          echo "<tr>" ;
          foreach($fields as $field) {
            echo "<td>$data[$field]</td>";
          }
          echo "</tr>" ;
        }
        echo "</table>" ;
      }
     ?>
     <?php
      if ( isset($conflist) && ($conflist != "") ) 
      {
        $imagetable=$conflist."_images" ;
        //TODO: Discover fields dynamically
        $fields = ['id', 'name', 'path', 'macAddr', 'date', 'mime'];
        echo "<br><table border='2px'>" ;
        echo "<tr>" ;
        foreach($fields as $field) {
          echo "<th>$field</th>";
        }
        echo "</tr>" ;
        foreach($database->query_assocs("SELECT * FROM ".$imagetable) as $data)
        {
          echo "<tr>" ;
          foreach($fields as $field) {
            echo "<td>$data[$field]</td>";
          }
          echo "</tr>" ;
        }
        echo "</table>" ;
      }
     ?>

   </div>
</body>

</html>
