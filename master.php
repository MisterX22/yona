<?php 

// Retrieving all required inputs
if(isset($_POST['name']))
{
  $name=$_POST['name'];
  if ($name == "")
    if(isset($_POST['conflist']))
      $name=$_POST['conflist'];
}
else
  if(isset($_POST['conflist']))
    $name=$_POST['conflist'];

if(isset($_GET['action']))
  $action=$_GET['action'] ;
else
  $action="N" ;

// Do we want to disconnect ? 
if ($action=="D")
  {
    $name="";
  }

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
                  id INT NOT NULL AUTO_INCREMENT, 
                  name VARCHAR(30),
                  firstreg BOOLEAN,
                  isconnected INT,
                  rtcid VARCHAR(30),
                  macAddr VARCHAR(30),
                  hostname VARCHAR(255),
                  waitformic BOOLEAN,
                  question VARCHAR(255),
                  questime TIME,
                  questove BOOLEAN,
                  votefor  VARCHAR(30),
                  votefo1  VARCHAR(30),
                  votefo2  VARCHAR(30),
                  votenum INT,
                  login DATETIME,
                  logout DATETIME,
                  PRIMARY KEY (id)
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
      function ResizeIframe(iframe)
        {
          var height = screen.height - 80 ;
          iframe.style.height = height + 'px' ;
        }

      function Hide (addr)
      	{
          document.getElementById(addr).style.visibility = "hidden";
          document.getElementById(addr).style.zIndex = "0";
        }
      function Show (addr) 
        {
          document.getElementById(addr).style.visibility = "visible";
          document.getElementById(addr).style.zIndex = "1";
          window.scrollTo(0,0);
        }
      function ChangeStyle (addr)
        {
          document.getElementById(addr).style.borderTop="thick solid grey";
        }
      function ResetStyle (addr)
        {
          document.getElementById(addr).style.borderTop="";
        }

      function Disabling (addr) {document.getElementById(addr).disabled = "disabled"}
      function Enabling (addr) {document.getElementById(addr).disabled = ""}
      function toggleValue() {
          if (document.getElementById("name").value == "")
            {
               Show("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
               Disabling("unsubmitname");
            }
          else
            {
               Hide("conf");
               showUsers();
            }
      }
      function showUsers() {
         if (document.getElementById("name").value == "")
           {
               Show("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("conf");
               Hide("demoContainer");
               Show("connectedUsers");
               Hide("connectControls");
               Hide("questionList");

               ChangeStyle("connectedUsers_button") ;
               ResetStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
           }
      }
      function showConnectControls() {
        if (document.getElementById("name").value == "")
           {
               Show("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("conf");
               Show("demoContainer");
               Hide("connectedUsers");
               Show("connectControls");
               Hide("questionList");

               ResetStyle("connectedUsers_button") ;
               ChangeStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
           }
      }
      function showQuestions() {
        if (document.getElementById("name").value == "")
           {
               Show("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Show("questionList");

               ResetStyle("connectedUsers_button") ;
               ResetStyle("connectControls_button") ;
               ChangeStyle("questionList_button") ;
           }
      }
      window.onload = function () {
          toggleValue();
      };
    </script>

    <style type="text/css">
          #demoContainer {
            position:relative;
            width: 100%;
            height: 100%;
          }
          #connectControls {
            /*float:left;*/
            /*width:400px;*/
            text-align:center;
            font-size: 100%;
            /*border: 2px solid black;*/
          }
          #connectButton, #disconnectButton {
            /*width:400px;*/
            text-align:center;
            font-size: 100%;
	        /*border-radius: 10px;
	        background-color:#e7e7e7;*/
          }
          #otherClients {
             height:200px;
             overflow-y:scroll;
          }
          #otherClients button {
             background-color:grey;
             font-size: 100%;
          }
          #otherClients button:active {
             background-color:pink;
             font-size: 150%;
          }
          #callerAudio {
             display:none; 
             height:10em;
             width:10em;
             position:absolute;
             top:140px;
             text-align:center;
             /*margin-left:10px;*/
          }
          #acceptCallBox {
             display:none;
             z-index:2;
             /*position:absolute;*/
             /*top:140px;*/
             margin: 0 auto ;
             width: 300px  ;
             border:red solid 2px;
             background-color:pink;
             padding:15px;
             font-size: 100%;
          }
          #callAcceptButton {
              text-align:center;
              font-size: 100%;
              border: 2px solid black;
          }
          #callRejectButton {
              text-align:center;
              font-size: 100%;
              border: 2px solid black;
          }

         #menutop {
              position: absolute;
              top: 0px;
              left: 0px;
              right: 0px;
              /*width: 100%;*/
              height: 30px;
              background-color : #183693;
              border-top: solid black 1px;
              /*padding: 5px;*/
              padding-left: 0px;
              padding-right: 0px;
              padding-top: 0px;
              padding-bottom: 10px;
              margin-left: 0px;
              margin-right: 0px;
              margin-top: 0px;
              margin-bottom: 0px;
              color: white;
              font-size: 150%;
              z-index:3;
         }

         #menutop td {
              padding-left: 10px;
              padding-top: 0px;
              text-align: center;
         }
         body>#menutop {position:fixed}

         #menubottom {
              position: absolute;
              bottom: 0px;
              left: 0px;
              right: 0px;
              width: 100%;
              height: 30px;
              background-color : #F2F2F2;
              border-top: solid black 1px;
              padding: 5px;
              padding-left: 5px;
              padding-top: 0px;
              color: white;
              z-index:3;
         }
         #menubottom td {
              padding-left: 0px;
              padding-top: 0px;
              text-align: center;
         }
         body>#menubottom {position:fixed}

         #conf, #connectedUsers, #connectControls, #questionList {
              visibility: hidden;
              position: absolute;
              top: 40px;
              left: 0px;
              width: 100%;
              height: 100%;
         }

         input[type=button], input[type=submit], input[type=reset], button {
              -webkit-appearance: none;
              background-color : #183693 ;
              color : white ;
              border-radius : 5px;
              font-size: 100%;
         }
         input[type=textarea] {
              background-color : white ;
              color : black ;
              border-radius : 5px;
              font-size: 100%;
         }
         textarea {
              background-color : white ;
              color : black ;
              border-radius : 5px;
              font-family: "Times New Roman";
              font-size: 100%;
         }
         body {
             /*margin: 0 0 5px 5px;
             padding: 0 0 5px 5px;*/
         }
         table {
             width: 100% ;
         }
    </style>
  </head>
  <body onload="setTimeout('connect()',4000)">
	
  <div name="menutop" id="menutop">
    <table width="100%">
    <tr>
    <td>Yona</td>
    <td><?php if ((isset($name)) AND ($name != "")) echo "$name" ; else echo "NOKIA"; ?></td>
    <td>
      <form name="byebye" id="byebye" method="post" 
          action="master.php?action=D&name=<?php if (isset($name)) echo $name?>">
        <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect" style="font-size: 50%;"
            onClick="document.getElementById("name").value='';">
      </form>
    </td>
    </tr>
    </table>
  </div>
  
  <div id="main">
    <!-- Main Content -->
    <form name="conf" id="conf" method="post" action="master.php"/>
      <table>
      <tr>
      <td><big><strong>Existing Conference : </strong></big></td>
      <td><select name="conflist" id="conflist" style="font-size: 150%">
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
      </select></td> 
      <td><input name="connectconf" id="connectconf" type="submit" value="ConnectConf"></td> 
      <td><input name="resetconf" id="resetconf" type="submit" value="ResetConf"></td> 
      <td><input name="deleteconf" id="deleteconf" type="submit" value="DeleteConf"></td>
      </tr>
      <tr>
      <td><big><strong>Conference Name:</strong></big></td>
      <!--<td><form name="createconf" id="createconf" method="post" action="master.php" style="font-size: 150%"/>!-->
      <td><input type="text" name="name" id="name" value="<?php if (isset($name)) echo $name;?>"></td>
      <td><input name="submitname" id="submitname" type="submit" value="Create"></td>
      <!--</form></td>!-->
      </tr>
      </table>
    </form>

    <br>
	
    <!--show-->
    <div id="demoContainer">
      <div id="connectControls">
        <div style="text-align: left;">
          <strong>Who wants to speak ?</strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Users requesting for the micro will appear</li><li>Give micro access by clicking on name</li><li>Access can be removed at any time by clicking on "Release it"</li></ul></i>
        </div>

        <center><button id="hangupButton" disabled="disabled" onclick="hangup()">Release it</button></center>
        <center><div id="iam">Not yet connected...</div></center>
        <br>
        <strong> <u>Waiting for Mic : </u></strong>
        <div id="ConnectedClients"></div>           
        <div id="otherClients"></div>
		
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
	
   <div id="connectedUsers">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING=auto 
            src="connected_master.php?name=Yona&conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>
	
   <div id="questionList">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING=auto 
            onload="javascript:ResizeIframe(this);"
            src="questions_master.php?conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>
	
  </div>
   <div name="menubottom" id="menubottom">
      <table width="100%">
        <tr>
          <td id="connectedUsers_button" name="connectedUsers_button"><img src="group.png" height="30px"   onclick="showUsers()"></td>
          <td id="connectControls_button" name="connectControls_button"><img src="micro.png" height="30px"   onclick="showConnectControls()"></td>
          <td id="questionList_button" name="questionList_button"><img src="QandA.png" height="30px"   onclick="showQuestions()"></td>
        </tr>
      </table>
  </div>

</body>
</html>
