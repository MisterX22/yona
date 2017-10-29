<?php 
include('includes/controller.php');
$controller = new Controller();
$database = $controller->database();

// Retrieving required inputs
$conflist=$_GET['conflist'];

$ipclient=$_SERVER['HTTP_X_FORWARDED_FOR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
//$macAddr=$lines[3];
$macAddr=$ipclient;

// Compute the server load to adjust refresh time
$load= sys_getloadavg() ;
if ( $load[0] > 60 )
  $refreshTime=120 ;
else
  $refreshTime=60 ;

$allquestions = "all";
$action="";
$refresh_choice="auto" ;

// retrieving post/get data
if (isset($_POST['allquestions']))
   $allquestions = $_POST['allquestions'];
if (isset($_GET['choice']))
   $allquestions = $_GET['choice'];
if (isset($_POST['refresh_choice']))
   $refresh_choice = $_POST['refresh_choice'];
if (isset($_GET['refresh']))
   $refresh_choice = $_GET['refresh'];

if (isset($_GET['action']))
   $action = $_GET['action'];

// Retrieving choice
$checkall='1';
if (isset($allquestions))
  {
    if ( $allquestions == "only" )
      {
         $checkonly='1';
      }
    else if ( $allquestions == "voted" )
      {
         $checkvoted='1';
      }
    else
      {
         $checkall='1';
      }
  }

$auto='1';
if (isset($refresh_choice))
  {
    if ( $refresh_choice == "auto" )
      {
         $auto='1';
      }
    else if ( $refresh_choice == "manual" )
      {
         $manual='1';
         $auto='0' ;
      }
  }

// retrieving myvote
$sql = "SELECT votefor, votefo1, votefo2, macAddr FROM ".$conflist." WHERE macAddr = '$macAddr' AND firstreg = '1' ";   
foreach($database->query_assocs($sql) as $madata) { 
    $myvote = $madata['votefor'] ;
    $myvot1 = $madata['votefo1'] ;
    $myvot2 = $madata['votefo2'] ;
} 

// How many vote remaining ?
$nbvote = 0 ;
$nbvotemax = 3 ;
if ( ( $myvote != "" ) )
  $nbvote = $nbvote + 1 ;
if ( ( $myvot1 != "" ) )
  $nbvote = $nbvote + 1 ;
if ( ( $myvot2 != "" ) )
  $nbvote = $nbvote + 1 ;
$remaining = $nbvotemax - $nbvote ;

// adjust text depending of the remaining vote
if ( $remaining == 0 )
  {
    $text = "Click to remove your vote" ;
    $confirmtext = "Want to remove vote for :" ;
  }
else
  {
    $text = "Click to add your vote" ;
    $confirmtext = "Want to vote for :" ;
  }

// Retrieving action on vote
if(isset($_GET['votefor']))
  {
    $votefor=$_GET['votefor'];
    $action=$_GET['action'];
    $sql2 = "SELECT macAddr, firstreg ,name, question FROM ".$conflist." WHERE id = '$votefor'";
    foreach($database->query_assocs($sql2) as $madata2) {
        $macSearch = $madata2['macAddr'] ;
        $firstreg = $madata2['firstreg'] ;
        $name = $madata2['name'] ;
        $question = $madata2['question'] ;
    }

    if ( $macSearch == $macAddr )
    {
      // This is my question want to remove it
      $text=$name." : ".$question."\n";
      $file = "trash/$conflist/delete_question.txt" ;
      file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
      if ($firstreg == '1') 
        {
          $sql = "UPDATE ".$conflist." SET question='', votenum = '0', questime=curtime(), questove = '0' WHERE id='$votefor'";
          $database->query($sql);
        }
      else 
        {
          $sql = "DELETE FROM $conflist WHERE id='$votefor'";
          $database->query($sql);
        }
      // remove all votes
      $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$votefor'";
      $database->query($sql2);
      $sql2 = "UPDATE ".$conflist." SET votefo1 = '' WHERE votefo1='$votefor'";
      $database->query($sql2);
      $sql2 = "UPDATE ".$conflist." SET votefo2 = '' WHERE votefo2='$votefor'";
      $database->query($sql2);
      $myvote = '' ;
      $myvot1 = '' ;
      $myvot2 = '' ;
      $remaining = 3 ;
    }
    else
    {
      // want to add a vote
      if ( $action == 'M' )
        {
          if ( $myvote == $votefor )
            {
              $sql = "UPDATE ".$conflist." SET votefor='' WHERE macAddr='$macAddr' AND firstreg='1'";
              $database->query($sql);
              $myvote = '' ;
              $remaining = $remaining + 1 ;
            }
          elseif ( $myvot1 == $votefor )
            {
              $sql = "UPDATE ".$conflist." SET votefo1='' WHERE macAddr='$macAddr' AND firstreg='1'";
              $database->query($sql);
              $myvot1 = '' ;
              $remaining = $remaining + 1 ;
            }
          elseif ( $myvot2 == $votefor )
            {
              $sql = "UPDATE ".$conflist." SET votefo2='' WHERE macAddr='$macAddr' AND firstreg='1'";
              $database->query($sql);
              $myvot2 = '' ;
              $remaining = $remaining + 1 ;
            }
        }
      // want to remove a vote
      else if ( ( $action == 'P' ) AND ( $remaining != 0 ) ) 
        {
          if ( $myvote == "" )
            {
              $sql = "UPDATE ".$conflist." SET votefor='$votefor' WHERE macAddr='$macAddr' AND firstreg='1'";
              $database->query($sql);
              $myvote = $votefor ;
              $remaining = $remaining - 1 ;
            }
          elseif ( $myvot1 == "" )
            {
              $sql = "UPDATE ".$conflist." SET votefo1='$votefor' WHERE macAddr='$macAddr' AND firstreg='1'";
              $database->query($sql);
              $myvot1 = $votefor ;
              $remaining = $remaining - 1 ;
            }
          elseif ( $myvot2 == "" )
            {
              $sql = "UPDATE ".$conflist." SET votefo2='$votefor' WHERE macAddr='$macAddr' AND firstreg='1'";
              $database->query($sql);
              $myvot2 = $votefor ;
              $remaining = $remaining - 1 ;
            }
        }
    }
    // we want to avoid double post on reload
    header('Location:  ./questions.php?conflist='.$conflist.'&choice='.$allquestions.'&refresh='.$refresh_choice);
    exit;
  }
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php
    if ( $auto == 1 )
      echo "<meta http-equiv='refresh' content='".$refreshTime."' URL=\"./questions.php?conflist=".$conflist."&choice=".$allquestions."&refresh=".$refresh_choice."\" />" ;
    ?>
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
       input[type=image] {
        -webkit-appearance: none;
        height: 30px;
       }
    </style>
    <script>
       //window.onload = alert(window.location.href) ;
       //no more working <?php // if ($reloadtopframe == "1") echo "top.frames.location.href='./index.php?conflist=".$conflist."'" ; ?>
    </script>

</head>

<body style="font-family: 'Arial';">

   <div id="questions">
     <strong><u> Questions :</u></strong>
     <form name="choice" id="choice" method="post" action="./questions.php?conflist=<?php if (isset($conflist)) echo $conflist?>">
       <table><tr>
       <!--<td><strong><u> Questions :</u></strong></td>-->
       <td><input type="radio" id="allquestions" name="allquestions" value="all" onclick="this.form.submit()" <?php if ( isset($checkall) ) echo "checked" ;?> >All</td>
       <td><input type="radio" id="allquestions" name="allquestions" value="only" onclick="this.form.submit()" <?php if ( isset($checkonly) ) echo "checked" ;?> >My questions</td>
       <td><input type="radio" id="allquestions" name="allquestions" value="voted" onclick="this.form.submit()" <?php if ( isset($checkvoted) ) echo "checked" ;?> >My points</td>
       </tr></table>
     </form>
     <i>Rules: <ul style="margin-top: 0px;"><li>you have <?php echo $remaining ; ?> point(s) left,</li><li><?php echo $text ; ?></li><li>Auto page refresh <?php echo $refreshTime ; ?> seconds</li></ul></i>
     <form name="refresh" id="refresh" method="post" action="./questions.php?conflist=<?php if (isset($conflist)) echo $conflist?>">
       <table><tr>
       <td style="text-align: center;">Refresh mode :</td>
       <td><input type="radio" id="refresh_choice" name="refresh_choice" value="auto" onclick="this.form.submit()" <?php if ( isset($auto) ) echo "checked" ;?> >Auto</td>
       <td><input type="radio" id="refresh_choice" name="refresh_choice" value="manual" onclick="this.form.submit()" <?php if ( isset($manual) ) echo "checked" ;?> >Manual</td>
       <?php if ( isset($manual) )
         echo "<td><input type='button' value='Refresh' onclick='this.form.submit()'></td>" ;
       ?>
       </tr></table>
     </form><br>
     <?php
      if ( isset($checkonly) ) 
        $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' AND macAddr='$macAddr' ORDER BY questove ASC, votenum DESC";   
      else if ( isset($checkvoted) )
        $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' AND ( (id = '$myvote') OR (id = '$myvot1') OR (id = '$myvot2') ) ORDER BY questove ASC, votenum DESC";   
      else
        $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' ORDER BY questove ASC, votenum DESC";   
      foreach($database->query_assocs($sql) as $data) 
      { 
        $macAddr2 = $data['macAddr'];
        $id = $data['id'];
        $monvote=$id;
        if ($data['questove'] == '1')
          {
            echo "<strike>" ;
          }
        $thisismyvote = 0 ;
        $nbvote = 0 ;
        if ( ( $myvote == $id ) or ( $myvot1 == $id ) or ( $myvot2 == $id ) )
          {
             echo "<strong>";
             if ( $myvote == $id ) $nbvote = $nbvote + 1 ;
             if ( $myvot1 == $id ) $nbvote = $nbvote + 1 ;
             if ( $myvot2 == $id ) $nbvote = $nbvote + 1 ;
             $thisismyvote = 1 ;
          }
        if ( $macAddr2 == $macAddr )
          {
          $actionimage="images/trash.png";
          $confirmtext="Want to delete :".$data['question'] ;
          echo "<font color='grey'><i>";
          }
        else {
          $actionimage="images/like.png";
          $confirmtext=$confirmtext.$data['question'] ;
        }
        echo "".$data['name']." at ".$data['questime']."";
        if ( $nbvote >= 1 ) echo "<img src='images/star.png' height='30px'></img>" ;
        if ( $nbvote >= 2 ) echo "<img src='images/star.png' height='30px'></img>" ;
        if ( $nbvote >= 3 ) echo "<img src='images/star.png' height='30px'></img>" ;
        echo "<br>";
        echo "".$data['question']."(" ; 
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefor = '".$id."')" ;
        $count = $database->query_arrays($sql2)[0][0];
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefo1 = '".$id."')" ;
        $row = $database->query_arrays($sql2)[0];
        $count = $count + $row[0];
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefo2 = '".$id."')";
        $row = $database->query_arrays($sql2)[0];
        $count = $count + $row[0];
        echo "".$count." points)<br>";
        if ($data['questove'] != '1' )
        {
          if ( ( $remaining !=0 ) or ( $thisismyvote == 1 ) or ($macAddr2 == $macAddr) )
          if  ( $macAddr2 == $macAddr )
          {
            // this is my question, want to remove it  ?
            echo "<input type='image' src=".$actionimage."  onclick=\"window.location.href='./questions.php?conflist=".$conflist."&votefor=".$monvote."&action=D';\">";
          }
          else
          {
            if ($remaining > 0) 
             echo "<input type='image' src=images/plus1.png  onclick=\"window.location.href='./questions.php?conflist=".$conflist."&votefor=".$monvote."&action=P'; \">";
            if ($thisismyvote == 1)
             echo "<input type='image' src=images/moins1.png  onclick=\"window.location.href='./questions.php?conflist=".$conflist."&votefor=".$monvote."&action=M'; \">";
          }
        }
        echo "<br><br>";
        $sql3 = "UPDATE ".$conflist." SET votenum='$count' WHERE id='$id'";
        $database->query($sql3);
        if ( $macAddr2 == $macAddr )
          {
            echo "</i></font>";
          }
        if ( ( $myvote == $id ) or ( $myvot1 == $id ) or ( $myvot2 == $id ) )
          {
             echo "</strong>";
          }
        if ($data['questove'] == '1')
          {
            echo "</strike>" ;
          }
      } 
      ?>
   </div>
</body>

</html>
