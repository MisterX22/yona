<?php 

$action=$_GET['action'] ;
$conflist=$_GET['conflist'];
$name=$_GET['name'];

// Gestion de la connection
if(isset($_POST['name']))
    $name=$_POST['name'];

if(isset($_POST['conflist']))
    $conflist=$_POST['conflist'];

if (isset($_POST['resetquestion']))
    {
        $remove=$_GET['name'] ;
        $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
        mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
        $sql = "UPDATE ".$conflist." SET question='' WHERE name='$remove'";   
        mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
        mysql_close();
    }

if(isset($_POST['submitquestion']))    
    {
    $yourquestion=$_POST['yourquestion'];
    $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
    mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
    $thequestion=mysql_real_escape_string($yourquestion) ;
    $sql = "INSERT INTO ".$conflist."(name, isconnected, rtcid, waitformic, question,login, logout) 
                                     VALUES('$name','1','','','$thequestion', now(),'') 
                                     ON DUPLICATE KEY UPDATE name='$name', isconnected='1' , 
                                                             question='$thequestion' , login=now()";   
    mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
    mysql_close(); 
    }
else
    {
        if (isset($name) AND isset($conflist))
        {
        $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
        mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
        $sql = "SELECT question FROM ".$conflist." WHERE name ='$name'";   
        $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
        while($madata = mysql_fetch_assoc($req)) 
            { 
            $yourquestion = $madata['question'];
            }
        mysql_close();
        }
    }

// Gestion de la deconnection
if ($action=="D")
    {
        $remove=$_GET['name'] ;
        $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
        mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
        $sql = "UPDATE ".$conflist." SET isconnected='0' , logout=now() WHERE name='$remove'";   
        mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
        mysql_close();
        $name="";
    }


if(empty($name))
    {
//Do nothing echo
    }
else
    {        
        $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
        mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
        $sql = "INSERT INTO ".$conflist."(name, isconnected, rtcid, waitformic, question,login, logout) 
                                     VALUES('$name','1','','','',now(),'') 
                                     ON DUPLICATE KEY UPDATE name='$name', isconnected='1' , login=now()";   
        mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
        mysql_close(); 
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
      Name: <?php echo "$name" ?>
      <input type="text" name="name" id="name" value="<?php echo $name; ?>">
      Conference : <?php echo "$conflist" ?>
      <select name="conflist" id="conflist">
<?php
// list conference
$db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
$sql = "show tables" ;
$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
while($table = mysql_fetch_array($req)) {
    echo "<option value='".$table[0]."'>".$table[0]."</option> " ;
}
mysql_close();        
?>
      </select>
      <input name="submitname" id="submitname" type="submit" value="Connect">
    </form>
    <br>
    <div id="demoContainer">
       <form name="byebye" id="byebye" method="post" action="index.php?action=D&name=<?php echo $name?>&conflist=<?php echo $conflist?>">
          <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect"
                        onClick="document.getElementById("name").value='';">
       </form>
       <br>
       <div id="connectControls">
           <button id="connectButton" onclick="connect(document.getElementById('name').value)">Want to speak ?</button>
           <br>
           <div id="iam">Not yet connected...</div><div id="rtcid"></div>
           <br>
           <h2 id="nbClients"></h2>
           <h2 id="conversation"></h2>
           <button id="disconnectButton" onclick="disconnect()">I have finished</button>              
        </div>
        <br>
        <div id="sendQuestions">
          <form name="question" id="question" method="post"  action="index.php?name=<?php echo $name?>&conflist=<?php echo $conflist?>" />Your Question:<br>
            <textarea rows="4" cols="50" name="yourquestion" id="yourquestion"><?php echo $yourquestion ;?></textarea><br>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
            <input name="resetquestion" id="resetquestion" type="submit"  value="Reset">
          </form>
        </div>
        <br>
        <div id="connectedUsers">
          <iframe style="overflow: hidden; height: 400px; width: 400px;" SCROLLING=auto src="connected.php?conflist=<?php echo $conflist?>">
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
