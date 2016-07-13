<?php 
// Retrieving all required inputs
if(isset($_POST['name']))
  $name=$_POST['name'];

if(isset($_POST['conflist']))
  $name=$_POST['conflist'];

// Managing table if required
if(empty($name))
  {
  }
else
  {
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $req = "SHOW TABLES LIKE '$name'" ;
    $res = mysqli_query($db,$req) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    if(mysqli_num_rows($res) == 1)
      {
        if (isset($_POST['resetconf']))
          {
            if(isset($_POST['conflist']))
              $conflist=$_POST['conflist'];
            $sql = "DELETE FROM `$conflist`";   
            mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
          }
        if (isset($_POST['deleteconf']))
          {
            if(isset($_POST['conflist']))
               $conflist=$_POST['conflist'];
            $sql = "DROP TABLE `$conflist`";   
            mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            $name = "";
          } 
      }
    else
      {
        $sql = "CREATE TABLE $name ( 
                  name VARCHAR(30),
                  isconnected BOOLEAN,
                  rtcid VARCHAR(30),
                  macAddr VARCHAR(30) NOT NULL PRIMARY KEY,
                  waitformic BOOLEAN,
                  question VARCHAR(255),
                  votefor VARCHAR(30),
                  votenum INT,
                  login DATETIME,
                  logout DATETIME
              )";   
        mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      }
      mysqli_close($db);  
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <!--skip-->
        <title>Audio Demo</title>
        <link rel="stylesheet" type="text/css" href="//<?php print $_SERVER{'SERVER_NAME'}; ?>:8443/easyrtc/easyrtc.css" />


        <!--show-->
        <!-- Assumes global locations for socket.io.js and easyrtc.js -->
        <script src="//<?php print $_SERVER{'SERVER_NAME'}; ?>:8443/socket.io/socket.io.js"></script>
        <script type="text/javascript" src="//<?php print $_SERVER{'SERVER_NAME'}; ?>:8443/easyrtc/easyrtc.js"></script>
        <script type="text/javascript" src="js/master.js"></script>

        <script type="text/javascript">
          function Hide (addr){document.getElementById(addr).style.visibility = "hidden";}
          function Show (addr) {document.getElementById(addr).style.visibility = "visible";}
          function Disabling (addr) {document.getElementById(addr).disabled = "disabled"}
          function Enabling (addr) {document.getElementById(addr).disabled = ""}
          function toggleValue(anId, testId, enableId1, enableId2) {
             if (document.getElementById(testId).value == "")
                {
                    Hide(anId);
                    //Enabling(enableId1) ;
                    //Enabling(enableId2) ;
                }
            else
                {
                    Show(anId);
                    //Disabling(enableId1);
                    //Disabling(enableId2)
                }
          }
          window.onload = function () {
            toggleValue("demoContainer", "name", "name", "submitname");
          };
        </script>

        <!-- Styles used within the demo -->
        <style type="text/css">
            #demoContainer {
                position:relative;
            }
            #connectControls {
                float:left;
                width:400px;
                /*text-align:center;*/
                border: 2px solid black;
            }
            #otherClients {
                height:50px;
                overflow-y:auto;
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
                top:100px;
                left:400px;
                border:red solid 2px;
                background-color:pink;
                padding:15px;
            }
        </style>
        <!--show-->
    </head>
    <body onload="setTimeout('connect()',4000)">
            <div id="main">
                <!-- Main Content -->
                <h1>Projet Yona <img src="micro.jpg" style ="float:left"> </h1>
                <form name="conf" id="conf" method="post" action="master.php"/>
                  <strong>Existing Conference : </strong><br>
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
                  <input name="connectconf" id="connectconf" type="submit" value="ConnectConf">
                  <input name="resetconf" id="resetconf" type="submit" value="ResetConf">
                  <input name="deleteconf" id="deleteconf" type="submit" value="DeleteConf">
                </form>
                <form name="createconf" id="createconf" method="post" action="master.php"/>Conf Name:<br>
                  <!--<input type="text" name="name" id="name" value="<?php if (isset($_POST['name'])){echo $_POST['name'];} ?>"><br><br>-->
                  <input type="text" name="name" id="name" value="<?php if (isset($name)) echo $name;?>">
                  <input name="submitname" id="submitname" type="submit" value="Create">
                </form>
                <br>
                <!--show-->
                <div id="demoContainer">
                    <div id="connectControls">
                        <center><button id="hangupButton" disabled="disabled" onclick="hangup()">Fin Connection</button></center>
                        <center><div id="iam">Not yet connected...</div></center>
                        <br>
                        <strong> <u>Waiting for Mic : </u></strong>
                        <div id="ConnectedClients"></div>           
                        <div id="otherClients"></div>
                        <iframe frameborder=0 style="overflow: hidden; height: 400px; width: 400px;"
                                          SCROLLING=auto src="connected_master.php?conflist=<?php if (isset($name)) echo $name?>">
                        </iframe>
                    </div>

                    <!-- Note... this demo should be updated to remove video references -->
                    <div id="videos">
                        <video id="callerAudio"></video>
                        <div id="acceptCallBox"> <!-- Should be initially hidden using CSS -->
                            <div id="acceptCallLabel"></div>
                            <button id="callAcceptButton" >Accept</button> <button id="callRejectButton">Reject</button>
                        </div>
                    </div>
                </div>
                <!--                   
                <div id="receiveMessageArea">
                   Received Messages:
                   <div id="conversation"></div>
                </div>
                --!>
            </div>
       <!--show-->
    </body>
</html>
