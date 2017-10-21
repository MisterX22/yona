<?php
  $conflist=$_GET['conflist'];

  if(isset($_GET['questove']))
  {
    $questove=$_GET['questove'];
    $db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'))  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,getenv('MYSQL_DB'))  or die('Erreur de selection '.mysqli_error($db));
    $sql = "UPDATE ".$conflist." SET questove = NOT(questove) WHERE id='$questove'";
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
     // remove all votes
    $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$questove'";
    mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    $sql2 = "UPDATE ".$conflist." SET votefo1 = '' WHERE votefo1='$questove'";
    mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    $sql2 = "UPDATE ".$conflist." SET votefo2 = '' WHERE votefo2='$questove'";
    mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    mysqli_close($db);
  }

  if(isset($_GET['trash']))
  {
    $trashid=$_GET['trash'];
    $db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'))  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,getenv('MYSQL_DB'))  or die('Erreur de selection '.mysqli_error($db));
    $sql = "DELETE from ".$conflist." WHERE id='$trashid'";
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
     // remove all votes
    $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$trashid'";
    mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    $sql2 = "UPDATE ".$conflist." SET votefo1 = '' WHERE votefo1='$trashid'";
    mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    $sql2 = "UPDATE ".$conflist." SET votefo2 = '' WHERE votefo2='$trashid'";
    mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    mysqli_close($db);
  }
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10, URL='./questions_master.php?conflist=<?php if (isset($conflist)) echo $conflist?>'" />
    <title>Connected users</title>
    <style type="text/css">
       #questions {
          font-size:100%;
          overflow-y:auto;
       }
       input[type=button] {
        -webkit-appearance: none;
        background-color: #183693 ;
        color: white ;
        border-radius: 5px;
        font-size:100%;
       }
      input[type=image] {
        -webkit-appearance: none;
        height: 30px;
       }
    </style>
    <script>
       //window.onload = alert(window.location.href) ;
    </script>



</head>

<body style="font-family: 'Arial';">
   <div id="questions">
     <strong><u> Questions :</u></strong><br>
     <i>Rules: <ul style="margin-top: 0px;"><li>Questions are sorted by number of votes</li><li>When a question is done, strike it by clicking on the <img src="images/check.png" height=10px> button</li></ul></i>
     <?php
      $db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'))  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,getenv('MYSQL_DB'))  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' ORDER BY questove ASC, votenum DESC";
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req))
      {
        $macAddr2 = $data['macAddr'];
        $id = $data['id'];
        $name2 = $data['name'];
        echo "".$data['name']." at ".$data['questime']."<br>";
        if ($data['questove'] == '1')
        {
          echo "<strike>" ;
        }
        $buttontext="images/check.png";
        echo $data['question']."(" ;
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE votefor = '".$id."'";
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row = mysqli_fetch_array($req2);
        $count = $row[0];
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefo1 = '".$id."')" ;
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row = mysqli_fetch_array($req2);
        $count = $count + $row[0];
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefo2 = '".$id."')";
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row = mysqli_fetch_array($req2);
        $count = $count + $row[0];
        echo "".$count." votes)";
        if ($data['questove'] == '1')
        {
          echo "</strike>" ;
        }
        echo "<br>" ;
        echo "<table><tr>";
        echo "<td><input type='image' src=".$buttontext."  style=\"height: 20px;\" onclick=\"window.location.href='./questions_master.php?conflist=".$conflist."&questove=".$id."'\"></td>";
        echo "<td><input type='image' src=\"images/trash.png\"  style=\"height: 20px;\" onclick=\"window.location.href='./questions_master.php?conflist=".$conflist."&trash=".$id."'\"></td>";
        echo "</tr></table>";
        echo "<br><br>";
      }
      mysqli_close($db);
     ?>
   </div>
</body>

</html>
