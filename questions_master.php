<?php
  include('includes/controller.php');
  $controller = new Controller();
  $database = $controller->database();
  $conflist=$_GET['conflist'];

  if(isset($_GET['questove']))
  {
    $questove=$_GET['questove'];
    $sql = "UPDATE ".$conflist." SET questove = NOT(questove) WHERE id='$questove'";
    $database->query($sql);
     // remove all votes
    $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$questove'";
    $database->query($sql2);
    $sql2 = "UPDATE ".$conflist." SET votefo1 = '' WHERE votefo1='$questove'";
    $database->query($sql2);
    $sql2 = "UPDATE ".$conflist." SET votefo2 = '' WHERE votefo2='$questove'";
    $database->query($sql2);
  }

  if(isset($_GET['trash']))
  {
    $trashid=$_GET['trash'];
    $sql = "DELETE from ".$conflist." WHERE id='$trashid'";
    $database->query($sql);
     // remove all votes
    $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$trashid'";
    $database->query($sql2);
    $sql2 = "UPDATE ".$conflist." SET votefo1 = '' WHERE votefo1='$trashid'";
    $database->query($sql2);
    $sql2 = "UPDATE ".$conflist." SET votefo2 = '' WHERE votefo2='$trashid'";
    $database->query($sql2);
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
      $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' ORDER BY questove ASC, votenum DESC";
      foreach($database->query_assocs($sql) as $data)
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
        $count = $database->query_arrays($sql2)[0][0];
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefo1 = '".$id."')" ;
        $row = $database->query_arrays($sql2)[0];
        $count = $count + $row[0];
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefo2 = '".$id."')";
        $row = $database->query_arrays($sql2)[0];
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
     ?>
   </div>
</body>

</html>
