<?php
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
     <form name="refresh" id="refresh" method="post" action="https://192.168.2.1/database.php?conflist=<?php if (isset($conflist)) echo 
$conflist?>">
       <table><tr><td><input type='button' value='Refresh' onclick='this.form.submit()'></td></tr></table>
     </form>

     <?php
      if ( isset($conflist) && ($conflist != "") ) 
      {
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT * FROM ".$conflist ;
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      echo "<br><table border='2px'>" ;
      echo "<tr>" ;
      echo "<th>id</th>";
      echo "<th>name</th>";
      echo "<th>macAddr</th>";
      echo "<th>isconnected</th>";
      echo "<th>firstreg</th>";
      //echo "<th>rtcid</th>";
      //echo "<th>waitformic</th>";
      echo "<th>question</th>";
      echo "<th>questime</th>";
      echo "<th>questove</th>";
      echo "<th>votefor</th>";
      echo "<th>votefo1</th>";
      echo "<th>votefo2</th>";
      echo "<th>votenum</th>";
      echo "<th>login</th>";
      echo "<th>logout</th>";
      echo "<th>hostname</th>";
      echo "</tr>" ;
      while($data = mysqli_fetch_assoc($req))
        {
           $name=$data['name'] ;
           $firstreg=$data['firstreg'] ;
           $isconnected=$data['isconnected'] ;
           //$rtcid=$data['rtcid'] ;
           $macAddr=$data['macAddr'] ;
           $hostname=$data['hostname'] ;
           //$waitformic=$data['waitformic'] ;
           $question=$data['question'] ;
           $questime=$data['questime'] ;
           $questove=$data['questove'] ;
           $votefor=$data['votefor'] ;
           $votefo1=$data['votefo1'] ;
           $votefo2=$data['votefo2'] ;
           $votenum=$data['votenum'] ;
           $login=$data['login'] ;
           $logout=$data['logout'] ;
           $id=$data['id'] ;

           echo "<tr>" ;
           echo "<td>".$id."</td>";
           echo "<td>".$name."</td>";
           echo "<td>".$macAddr."</td>";
           echo "<td>".$isconnected."</td>";
           echo "<td>".$firstreg."</td>";
           //echo "<td>".$rtcid."</td>";
           //echo "<td>".$waitformic."</td>";
           echo "<td>".$question."</td>";
           echo "<td>".$questime."</td>";
           echo "<td>".$questove."</td>";
           echo "<td>".$votefor."</td>";
           echo "<td>".$votefo1."</td>";
           echo "<td>".$votefo2."</td>";
           echo "<td>".$votenum."</td>";
           echo "<td>".$login."</td>";
           echo "<td>".$logout."</td>";
           echo "<td>".$hostname."</td>";
           echo "</tr>" ;

        }
      echo "</table>" ;
      mysqli_close($db);
      }
     ?>

   </div>
</body>

</html>
