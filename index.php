<?php 
if(isset($_GET['action']))
    {
    $action=$_GET['action'] ;
    $conflist=$_GET['conflist'];
    }
else
    {
    $action="";
    $conflist="";
    }
    

// Gestion de la connection
if(isset($_POST['name']))
    $name=$_POST['name'];
else
    $name="";

if(isset($_POST['conflist']))
    $conflist=$_POST['conflist'];

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
      function toggleValue(anId, testId, enableId1, enableId2) {
          if (document.getElementById(testId).value == "")
              {
                  Hide(anId);
                  Enabling(enableId1) ;
                  Enabling(enableId2) ;
              }
          else
              {
                  Show(anId);
                  Disabling(enableId1);
                  Disabling(enableId2)
              }
      }
      window.onload = function () {
          toggleValue("demoContainer", "name", "name", "submitname");
      };
    </script>

<!--
    <script type="text/javascript">
      var auto_refresh = setInterval(
        function ()
        {
           $('#ConnectedUsers').load('index.php').fadeIn("slow");
        }, 10000); // rafraichis toutes les 10000 millisecondes
    </script>
--!>
        
    <!-- Styles used within the demo -->
    <style type="text/css">
          #demoContainer {
            position:relative;
          }
          #connectControls {
            /*float:left;*/
            width:500px;
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

<body bgcolor="blue">
  <div id="main">
    <h1>Project-X : client</h1>
    <form name="whoami" id="whoami" method="post" action="index.php"/>Name:<br>
      <input type="text" name="name" id="name" value="<?php if (isset($_POST['name'])){echo $_POST['name'];} ?>"><br>
      <select name="conflist" id="conflist">
<?php
// list conference
$db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
$sql = "show tables" ;
$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
while($table = mysql_fetch_array($req)) {
    //echo($table[0] . "<BR>");
    echo "<option value='".$table[0]."'>".$table[0]."</option> " ;
}
mysql_close();        
?>
      </select>
      <br><br>
      <input name="submitname" id="submitname" type="submit" value="Connect">
    </form>
    <br><br>
    <div id="demoContainer">
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
<!--
        <div id="sendQuestions">
          <form name="question" id="question" method="post"/>Your Question:<br>
            <textarea name="yourquestion" id="yourquestion"></textarea>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
          </form>
        </div>
--!>
        <br>
        <div id="connectedUsers">
          <form name="byebye" id="byebye" method="post" action="index.php?action=D&name=<?php echo $name?>&conflist=<?php echo $conflist?>">
             <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect"
                           onClick="document.getElementById("name").value='';">
          </form>

          <h2> Connected Users</h2>
          <?php
            $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
            mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
            $sql = "SELECT name FROM ".$conflist." WHERE isconnected ='1'";   
            $req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
            while($data = mysql_fetch_assoc($req)) 
            { 
               echo $data['name']."<br>" ; 
            } 
            mysql_close();
          ?>

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
