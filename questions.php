<?php 

// Retrieving required inputs
$conflist=$_GET['conflist'];

$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
$macAddr=$lines[3];


// Retrieving vote
if(isset($_GET['votefor']))
  {
    $votefor=$_GET['votefor'];
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $sql = "UPDATE ".$conflist." SET votefor='$votefor' WHERE macAddr='$macAddr'";
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db);
  }
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10, URL='questions.php?conflist=<?php if (isset($conflist)) echo $conflist?>'" />
    <title>Questions</title>
    <style type="text/css">
       #questions {
          /*height:100%;
          overflow-y:auto;*/
       }
       input[type=button] {
        -webkit-appearance: none;
        background-color: #183693 ;
        color: white ;
        border-radius: 5px;
       }
    </style>
    <script>
       //window.onload = alert(window.location.href) ;
    </script>

</head>

<body>

   <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT votefor, macAddr FROM ".$conflist." WHERE macAddr = '$macAddr'";   
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($madata = mysqli_fetch_assoc($req)) 
      { 
        $myvote = $madata['votefor'] ;
      } 
      mysqli_close($db);
   ?>

   <div id="questions">
     <strong><u> Questions :</u></strong><br>
     <i>Rules: <ul style="margin-top: 0px;"><li>Each user can vote for one question (just click on "Need to know")</li><li>Vote can be changed at any time</li><li>Questions are ordered by number of votes</li><li>Questions already answered will be striked</ul></i>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' ORDER BY questove ASC, votenum DESC";   
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req)) 
      { 
        $macAddr2 = $data['macAddr'];
        if ( $myvote == $macAddr2 ) {
           echo "<strong>";
        }
        if ($data['questove'] == '1')
        {
          echo "<strike>" ;
          $Buttontext="'Done'";
        }
        else {
          $Buttontext="'Need to known'";
        }
        echo "".$data['name']." at ".$data['questime']."<br>";
        //echo "<a href='questions.php?conflist=".$conflist."&votefor=".$macAddr2."&name=".$name."&action='>".$data['question']."(" ; 
        echo "".$data['question']."(" ; 
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE votefor = '".$macAddr2."'";
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row = mysqli_fetch_array($req2);
        $count = $row[0];
        //echo "".$count." votes)</a><br>";
        echo "".$count." votes)<br>";
        if ($data['questove'] != '1')
        {
          echo "<input type='button' value=".$Buttontext." onclick=\"window.location.href='questions.php?conflist=".$conflist."&votefor=".$macAddr2."&name=".$name."&action='\">";
        }
        echo "<br><br>";
        $sql3 = "UPDATE ".$conflist." SET votenum='$count' WHERE macAddr='$macAddr2'";
        mysqli_query($db,$sql3) or die('Erreur SQL !'.$sql3.'<br>'.mysqli_error($db));
        if ( $myvote == $macAddr2 ) {
           echo "</strong>";
        }
        if ($data['questove'] == '1')
        {
          echo "</strike>" ;
        }
      } 
      mysqli_close($db);
      ?>
   </div>
</body>

</html>
