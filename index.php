<?php 

// Retrieving required inputs

if(isset($_GET['action']))
  $action=$_GET['action'] ;
else
  $action="" ;

if(isset($_GET['conflist']))
  $conflist=$_GET['conflist'];
if(isset($_POST['conflist']))
  $conflist=$_POST['conflist'];

if(isset($_GET['name']))
  $name=$_GET['name'];
if(isset($_POST['name']))
  $name=$_POST['name'];

$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
$macAddr=$lines[3];

// Do we need to reset the question ?
if (isset($_POST['resetquestion']))
  {
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "UPDATE ".$conflist." SET question='', votenum=0 WHERE macAddr='$macAddr'";   
      mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      mysqli_close($db);
  }

// Do we need to register or read a question ?
if(isset($_POST['submitquestion']))    
  {
    $yourquestion=$_POST['yourquestion'];
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $thequestion=mysqli_real_escape_string($db,$yourquestion) ;
    $sql = "UPDATE ".$conflist." SET question='$thequestion', votenum=0 WHERE macAddr='$macAddr'";
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db); 
  }
else
  {
    if (isset($name) AND isset($conflist))
      {
         $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
         mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
         $sql = "SELECT question FROM ".$conflist." WHERE macAddr = '$macAddr'";   
         $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
         while($madata = mysqli_fetch_assoc($req)) 
           { 
             $yourquestion = $madata['question'];
           }
          mysqli_close($db);
      }
  }

// Do we want to disconnect ? 
if ($action=="D")
  {
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $sql = "UPDATE ".$conflist." SET isconnected='0' , logout=now() WHERE macAddr='$macAddr'";   
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db);
    $name="";
  }

// Connection
if(isset($_POST['name']))
  {
     $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
     mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
     $thename=mysqli_real_escape_string($db,$name) ;
     $sql = "INSERT INTO ".$conflist."(name, isconnected, rtcid, macAddr,waitformic, question,login, logout) 
                                   VALUES('$thename','1','','$macAddr','','',now(),'') 
                                   ON DUPLICATE KEY UPDATE name='$name', isconnected='1' , login=now()";   
      mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      mysqli_close($db); 
  }
?>
   
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <!--skip-->
    <title>Audio Demo</title>
    <link rel="stylesheet" type="text/css" href="//<?php print $_SERVER{'SERVER_NAME'}; ?>:8443/easyrtc/easyrtc.css" />
    <meta name="viewport" content="width=device-width"/>

    <!--show-->
    <!-- Assumes global locations for socket.io.js and easyrtc.js -->
    <script src="//<?php print $_SERVER{'SERVER_NAME'}; ?>:8443/socket.io/socket.io.js"></script>
    <script type="text/javascript" src="//<?php print $_SERVER{'SERVER_NAME'}; ?>:8443/easyrtc/easyrtc.js"></script>
    <script type="text/javascript" src="js/client.js"></script>

    <script type="text/javascript">
      function Hide (addr){document.getElementById(addr).style.visibility = "hidden";}
      function Show (addr) {document.getElementById(addr).style.visibility = "visible";}
      function Disabling (addr) {document.getElementById(addr).disabled = "disabled"}
      function Enabling (addr) {document.getElementById(addr).disabled = ""}
      function toggleValue(anId, testId, enableId1, enableId2, enableId3) {
          if (document.getElementById(testId).value == "")
              {
                  Hide(anId);
                  Enabling(enableId1) ;
                  Enabling(enableId2) ;
                  Enabling(enableId3) ;
              }
          else
              {
                  Show(anId);
                  //Disabling(enableId1);
                  //Disabling(enableId2) ;
                  Hide (enableId3) ;
                  Hide (enableId2) ;
                  Hide (enableId1) ;
              }
      }
      window.onload = function () {
          toggleValue("demoContainer", "name", "name", "submitname", "conflist");
      };
    </script>
        
    <!-- Styles used within the demo -->
    <style type="text/css">
          #demoContainer {
            position:relative;
          }
          #connectControls {
            /*float:left;*/
            width:400px;
            text-align:center;
            border: 2px solid black;
          }
          #connectButton, #disconnectButton  {
            /*width:400px;*/
            text-align:center;
            font-size: 150%;
	        border-radius: 10px;
	        background-color:#e7e7e7;
          }
          #whoami, #name, #submitname, #byebye, #unsubmitname {
            font-size: 100%;
	      }
          #otherClients {
             height:200px;
             overflow-y:scroll;
          }
          #callerAudio {
             /* display:none; */
             height:10em;
             width:10em;
             margin-left:10px;
          }
          #acceptCallBox {
             display:none;
             z-index:2;
             position:absolute;
             top:0px;
             left:0px;
             border:red solid 2px;
             background-color:pink;
             padding:15px;
             font-size: 100%;
          }
          #callAcceptButton{
              text-align:center;
              font-size: 100%;
              border: 2px solid black;
          }
          #callRejectButton{
              text-align:center;
              font-size: 100%;
              border: 2px solid black;
          }
    </style>

</head>

<body>
  <div id="main">
    <h1>Projet Yona <img src="micro.jpg" style ="float:left"> </h1> 
    <form name="whoami" id="whoami" method="post" action="index.php"/>
      <strong>Configuration : </strong><br>
      Name: <?php if (isset($name)) echo "$name" ?>
      <input type="text" name="name" id="name" maxlength="15" value="<?php if (isset($name)) echo $name; ?>">
      <br>Conference : <?php if (isset($conflist)) echo "$conflist" ?>
      <select name="conflist" id="conflist">
      <?php
         // list conference
         $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
         mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
         $sql = "show tables" ;
         $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
         while($table = mysqli_fetch_array($req)) {
             echo "<option value='".$table[0]."'>".$table[0]."</option> " ;
         }
         mysqli_close($db);        
      ?>
      </select>
      <input name="submitname" id="submitname" type="submit" value="Connect">
    </form>
    <div id="demoContainer">
       <form name="byebye" id="byebye" method="post" action="index.php?action=D&name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>">
          <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect"
                        onClick="document.getElementById("name").value='';">
       </form>
       <br>
        <div id="sendQuestions">
          <form name="question" id="question" method="post"  action="index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>" />Your Question:<br>
            <textarea rows="4" cols="50" name="yourquestion" id="yourquestion"><?php if (isset($question)) echo $yourquestion ;?></textarea><br>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
            <input name="resetquestion" id="resetquestion" type="submit"  value="Reset">
          </form>
        </div>
       <div id="connectControls">
           <button id="connectButton" onclick="connect(document.getElementById('name').value)">Micro request</button>
           <div id="iam">Not yet connected...</div><div id="rtcid"></div>
           <div id="nbClients"></div>
           <div id="conversation"></div>
           <button id="disconnectButton" onclick="disconnect()">Micro release</button>              
        </div>
        <div id="connectedUsers">
          <iframe style="overflow: hidden; height: 400px; width: 400px;" SCROLLING=auto src="connected.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
        <!-- Note... this demo should be updated to remove video references -->
        <div id="videos">
            <video id="callerAudio"></video>
            <div id="acceptCallBox">
                <div id="acceptCallLabel"></div>
                <br><br>
                <button id="callAcceptButton" >Accept</button> or <button id="callRejectButton">Reject</button>
            </div>
         </div>
    </div>
  </div>              
</body>

</html>
