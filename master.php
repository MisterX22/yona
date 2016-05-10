<?php 
// Gestion de la connection
if(isset($_POST['name']))
    $name=$_POST['name'];
//else
//    $name=$_GET['name'];

if(empty($name))
    {
//Do nothing echo '<font color="red">Attention, name undefined !</font>';
    }
else
    {
// Table creation
        $db = mysql_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysql_error());
        mysql_select_db('projectX',$db)  or die('Erreur de selection '.mysql_error());
        $req = "SHOW TABLES LIKE '$name'" ;
        $res = mysql_query($req) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
        if(mysql_num_rows($res) == 1)
        {
            if (isset($_POST['resetconf']))
            {
            $sql = "DELETE FROM `$name`";   
            mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
            }
        }
        else
        {
        $sql = "CREATE TABLE $name ( 
                    name VARCHAR(30) NOT NULL PRIMARY KEY,
                    isconnected BOOLEAN,
                    rtcid VARCHAR(30),
                    waitformic BOOLEAN,
                    question VARCHAR(255),
                    login DATETIME,
                    logout DATETIME
                )";   
        mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
        }
        mysql_close();  
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
                height:200px;
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
                <form name="createconf" id="createconf" method="post" action="master.php"/>Conf Name:<br>
                <!--<input type="text" name="name" id="name" value="<?php if (isset($_POST['name'])){echo $_POST['name'];} ?>"><br><br>-->
                <input type="text" name="name" id="name" value="<?php echo $name;?>"><br><br>
                <input name="submitname" id="submitname" type="submit" value="Connect">
                <input name="resetconf" id="resetconf" type="submit" value="ResetConf">
                </form>
                <!--show-->
                <div id="demoContainer">
                    <div id="connectControls">
                        <center><button id="hangupButton" disabled="disabled" onclick="hangup()">Fin Connection</button></center>
                        <center><div id="iam">Not yet connected...</div></center>
                        <br>
                        <iframe frameborder=0 style="overflow: hidden; height: 400px; width: 400px;" SCROLLING=auto src="connected.php?conflist=<?php echo $name?>">
                        </iframe>
                        <br>
                        <strong> <u>Waiting for Mic : </u></strong>
                        <div id="ConnectedClients"></div>           
                        <div id="otherClients"></div>
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
