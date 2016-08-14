<?php 

// Retrieving required inputs
$conflist=$_GET['conflist'];

$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
$macAddr=$lines[3];

$load= sys_getloadavg() ;
if ( $load[0] > 60 )
  $refreshTime=20 ;
else
  $refreshTime=10 ;

if (isset($_POST['allquestions']))
   $allquestions = $_POST['allquestions'];
if (isset($_GET['choice']))
   $allquestions = $_GET['choice'];

// Retrieving choice
if (isset($allquestions))
  {
    if ( $allquestions == "only" )
      {
         $checkonly='1';
      }
    else
      {
         $checkall='1';
      }
  }

$db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
$sql = "SELECT votefor, votefo1, votefo2, macAddr FROM ".$conflist." WHERE macAddr = '$macAddr' AND firstreg = '1' ";   
$req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
while($madata = mysqli_fetch_assoc($req)) 
  { 
    $myvote = $madata['votefor'] ;
    $myvot1 = $madata['votefo1'] ;
    $myvot2 = $madata['votefo2'] ;
  } 
mysqli_close($db);
$nbvote = 0 ;
$nbvotemax = 3 ;
if ( ( $myvote != "" ) )
  $nbvote = $nbvote + 1 ;
if ( ( $myvot1 != "" ) )
  $nbvote = $nbvote + 1 ;
if ( ( $myvot2 != "" ) )
  $nbvote = $nbvote + 1 ;
$remaining = $nbvotemax - $nbvote ;
if ( $remaining == 0 )
  $text = "Click on one question to remove your vote" ;
else
  $text = "Click on one question to add your vote" ;

// Retrieving vote
if(isset($_GET['votefor']))
  {
    $votefor=$_GET['votefor'];
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));

    $sql2 = "SELECT macAddr, firstreg FROM ".$conflist." WHERE id = '$votefor'";
    $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    while($madata2 = mysqli_fetch_assoc($req2))
      {
        $macSearch = $madata2['macAddr'] ;
        $firstreg = $madata2['firstreg'] ;
      }


    if ( $macSearch == $macAddr )
    {
      // This is my question want to remove it
      if ($firstreg == '1') 
        {
          $sql = "UPDATE ".$conflist." SET question='', votenum = '0', questime=curtime(), questove = '0' WHERE id='$votefor'";
          mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
        }
      else 
        {
          $sql = "DELETE FROM `$conflist` WHERE id='$votefor'";
          mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
        }
      // remove all votes
      $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$votefor'";
      mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
      $sql2 = "UPDATE ".$conflist." SET votefo1 = '' WHERE votefo1='$votefor'";
      mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
      $sql2 = "UPDATE ".$conflist." SET votefo2 = '' WHERE votefo2='$votefor'";
      mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
    }
    else
    {
      if ( $remaining == 0 )
        {
          if ( $myvote == $votefor )
            {
              $sql = "UPDATE ".$conflist." SET votefor='' WHERE macAddr='$macAddr' AND firstreg='1'";
              mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            }
            elseif ( $myvot1 == $votefor )
            {
              $sql = "UPDATE ".$conflist." SET votefo1='' WHERE macAddr='$macAddr' AND firstreg='1'";
              mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            }
            elseif ( $myvot2 == $votefor )
            {
              $sql = "UPDATE ".$conflist." SET votefo2='' WHERE macAddr='$macAddr' AND firstreg='1'";
              mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            }
        }
      else 
        {
          if ( $myvote == "" )
            {
              $sql = "UPDATE ".$conflist." SET votefor='$votefor' WHERE macAddr='$macAddr' AND firstreg='1'";
              mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            }
          elseif ( $myvot1 == "" )
            {
              $sql = "UPDATE ".$conflist." SET votefo1='$votefor' WHERE macAddr='$macAddr' AND firstreg='1'";
              mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            }
          elseif ( $myvote2 == "" )
            {
              $sql = "UPDATE ".$conflist." SET votefo2='$votefor' WHERE macAddr='$macAddr' AND firstreg='1'";
              mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            }
        }
    }
    mysqli_close($db);
  }
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="<?php echo $refreshTime; ?>, URL='questions.php?conflist=<?php if (isset($conflist)) echo $conflist?>&choice=<?php if (isset($allquestions)) echo $allquestions?>'" />
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
    </script>

</head>

<body>

<?php
  //echo "Myvote : ".$myvote." / ".$myvot1." / ".$myvot2 ;
?>

   <div id="questions">
     <strong><u> Questions :</u></strong><br>
     <i>Rules: <ul style="margin-top: 0px;"><li>you have <?php echo $remaining ; ?> votes left,</li><li><?php echo $text ; ?></li></ul></i>
     <form name="choice" id="choice" method="post" action="questions.php?conflist=<?php if (isset($conflist)) echo $conflist?>">
       <input type="radio" id="allquestions" name="allquestions" value="all" onclick="this.form.submit()" <?php if ( isset($checkall) ) echo "checked" ;?> >All questions
       <input type="radio" id="allquestions" name="allquestions" value="only" onclick="this.form.submit()" <?php if ( isset($checkonly) ) echo "checked" ;?> >Only my questions
     </form><br>
     <?php
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      if ( isset($checkonly) ) 
        $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' AND macAddr='$macAddr' ORDER BY questove ASC, votenum DESC";   
      else
        $sql = "SELECT id, question , name, macAddr, questime, questove FROM ".$conflist." WHERE question !='' ORDER BY questove ASC, votenum DESC";   
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($data = mysqli_fetch_assoc($req)) 
      { 
        $macAddr2 = $data['macAddr'];
        $id = $data['id'];
        $monvote=$id;
        if ($data['questove'] == '1')
          {
            echo "<strike>" ;
          }
        if ( ( $myvote == $id ) or ( $myvot1 == $id ) or ( $myvot2 == $id ) )
          {
             echo "<strong>";
          }
        if ( $macAddr2 == $macAddr )
          {
          $Buttontext="trash.png";
          echo "<font color='red'>";
          }
        else {
          $Buttontext="like.png";
        }
        echo "".$data['name']." at ".$data['questime']."<br>";
        echo "".$data['question']."(" ; 
        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE (votefor = '".$id."')" ;
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
        echo "".$count." votes)<br>";
        if ($data['questove'] != '1')
        {
          echo "<input type='image' src=".$Buttontext."  onclick=\"window.location.href='questions.php?conflist=".$conflist."&votefor=".$monvote."&action='; top.frames.location.href = top.frames.location.href;\">";
        }
        echo "<br><br>";
        $sql3 = "UPDATE ".$conflist." SET votenum='$count' WHERE id='$id'";
        mysqli_query($db,$sql3) or die('Erreur SQL !'.$sql3.'<br>'.mysqli_error($db));
        if ( $macAddr2 == $macAddr )
          {
          echo "</font>";
          }
        if ( ( $myvote == $id ) or ( $myvot1 == $id ) or ( $myvot2 == $id ) ) {
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
