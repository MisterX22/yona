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
      $sql = "UPDATE ".$conflist." SET question='', votenum=0, questime=curtime()  WHERE macAddr='$macAddr'";   
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
    $sql = "UPDATE ".$conflist." SET question='$thequestion', votenum=0, questime=curtime() WHERE macAddr='$macAddr'";
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
      function ResizeIframe(iframe)
        {
          //var iframeBody = (iframe.contentDocument) ? iframe.contentDocument.body : iframe.contentWindow.document.body; 
          //var height = (iframeBody.scrollHeight < iframeBody.offsetHeight) ? iframeBody.scrollHeight : iframeBody.offsetHeight;
          //height = height + 10;
          //$(iframe).height(height);
          var height = screen.height - 80 ;
          //alert(height) ;
          iframe.style.height = height + 'px' ;
        }

      function AdjustIframeHeightOnLoad()
        {
          var screenHeight = $(window).height();
          var iframeContentHeight;
          var iframeContentwidth;

          iframeContentHeight = document.getElementById("iframe_ObjectLink").contentWindow.document.body.scrollHeight;

          iframeContentwidth = document.getElementById("iframe_ObjectLink").contentWindow.document.body.scrollWidth;


          if (iframeContentHeight > (screenHeight - 47))
            {
              document.getElementById("objectionMenuContentContainer_div").style.height = (screenHeight - 27) + "px";
              document.getElementById("iframe_ObjectLink").height = (screenHeight - 47);
              document.getElementById("iframe_ObjectLink").contentWindow.document.documentElement.style.overflowY = "scroll";
            }
          else
            {
              document.getElementById("objectionMenuContentContainer_div").style.height = iframeContentHeight + "px";
              document.getElementById("iframe_ObjectLink").height = (iframeContentHeight - 25);
              document.getElementById("iframe_ObjectLink").contentWindow.document.documentElement.style.overflowY = "hidden";
            }

          if (iframeContentwidth > 883)
            document.getElementById("iframe_ObjectLink").contentWindow.document.documentElement.style.overflowX = "scroll";
          else
            document.getElementById("iframe_ObjectLink").contentWindow.document.documentElement.style.overflowX = "hidden";
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
        }
      function Disabling (addr) {document.getElementById(addr).disabled = "disabled"}
      function Enabling (addr) {document.getElementById(addr).disabled = ""}
      function toggleValue() {
          if (document.getElementById("name").value == "")
            {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
               Disabling("unsubmitname");
            }
          else
            {
               Hide("whoami");
               showUsers();
            }
      }
      function showUsers() {
         if (document.getElementById("name").value == "")
           {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Show("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
           }
      }
      function showSendQuestion() {
        if (document.getElementById("name").value == "")
           {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Show("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
           }
      }
      function showConnectControls() {
        if (document.getElementById("name").value == "")
           {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Show("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Show("connectControls");
               Hide("questionList");
           }
      }
      function showQuestions() {
        if (document.getElementById("name").value == "")
           {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("sendQuestions");
               Hide("connectControls");
               Show("questionList");
           }
      }
      window.onload = function () {
          toggleValue();
      };
    </script>
        
    <!-- Styles used within the demo -->
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
            /*border: 2px solid black;*/
          }
          #connectButton, #disconnectButton {
            /*width:400px;*/
            text-align:center;
            font-size: 150%;
	    /*border-radius: 10px;
	    background-color:#e7e7e7;*/
          }
          #otherClients {
             height:200px;
             overflow-y:scroll;
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
              width: 100%;
              height: 30px;
              background-color : #183693;
              border-top: solid black 1px;
              padding: 5px;
              padding-left: 5px;
              padding-top: 0px;
              color: white;
              font-size: 150%;
              z-index:3;
         }
         #menutop td {
              padding-left: 20px;
              padding-top: 0px;
         }
         body>#menutop {position:fixed}
         #menutop td {
              padding-left: 20px;
              padding-top: 0px;
         }

         #menubottom {
              position: absolute;
              bottom: 0px;
              left: 0px;
              right: 0px;
              width: 100%;
              height: 30px;
              background-color : #183693;
              border-top: solid black 1px;
              padding: 5px;
              padding-left: 5px;
              padding-top: 0px;
              color: white;
              z-index:3;
         }
         #menubottom td {
              padding-left: 20px;
              padding-top: 0px;
         }
         body>#menubottom {position:fixed}
         #menubottom td {
              padding-left: 20px;
              padding-top: 0px;
         }

         #whoami, #sendQuestions, #connectedUsers, #connectControls, #questionList {
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
              font-size: 150%;
         }
         input[type=textarea] {
              background-color : white ;
              color : black ;
              border-radius : 5px;
              font-size: 150%;
         }

    </style>

</head>

<body>

<div name="menutop" id="menutop">
<table width="100%">
<tr>
<td>Yona</td>
<td><?php if ((isset($name)) AND ($name != "")) echo "$name" ; else echo "NOKIA"; ?></td>
<td>
  <form name="byebye" id="byebye" method="post" 
    action="index.php?action=D&name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>">
     <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect" style="font-size: 50%;"
                   onClick="document.getElementById("name").value='';">
  </form>
</td>
</tr>
</table>
</div>

  <div name="main" id="main">

    <form name="whoami" id="whoami" method="post" action="index.php"/>
      <table>
      <tr>
      <td>Name : <?php if (isset($name)) echo "$name" ?></td>
      <td><input type="text" placeholder="Name" name="name" id="name" maxlength="20" style="font-size: 100%; width:200px;" 
                                      value="<?php if (isset($name)) echo $name; ?>"></td>
      </tr>
      </tr>
      <tr>
      <td>Conference : </td>
      <td><select name="conflist" id="conflist" style="background-color:white;width:200px;font-size: 100%;">
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
      </tr>
      </table>
      <center><input name="submitname" id="submitname" type="submit" value="Register" 
                                   style="color:white;background-color:#183693;font-size: 150%;" ></center>
      <br><center><big><strong>Welcome to Yona<br>Please Register</strong></big></center>
    </form>

    <div id="demoContainer">
        <div id="sendQuestions">
          <form name="question" id="question" method="post"  
            action="index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>" />
            <textarea style="width: 100%;height: auto;font-size: 100%;" rows="10" cols="50" placeholder="Your question" name="yourquestion" id="yourquestion"><?php if (isset($yourquestion)) echo $yourquestion ;?></textarea><br>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
            <input name="resetquestion" id="resetquestion" type="submit" value="Reset">
          </form>
        </div>
        <div id="connectControls">
           <button id="connectButton" style='color: white; background-color : #183693' onclick="connect(document.getElementById('name').value)">Ask Micro</button>
           <br><br>
           <button id="disconnectButton" style='color: white; background-color : #183693' onclick="disconnect()">Release Micro</button>              
           <br><br>
           <div id="iam">Not yet connected...</div><div id="rtcid"></div>
           <div id="nbClients"></div>
           <div id="conversation"></div>
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
        <div id="connectedUsers">
          <iframe style="border: none; height: 800px; width: 100%;" SCROLLING=auto 
             onload="javascript:ResizeIframe(this);"
             src="connected.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
        <div id="questionList">
          <iframe style="border: none; overflow: visible; width: 100%; height: 100%;" SCROLLING=auto 
             onload="javascript:ResizeIframe(this);"
             src="questions.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
    </div>
  </div>              

<div name="menubottom" id="menubottom">
<table width="100%">
<tr>
<td><img src="group.png" height="30px"   onclick="showUsers()"></td>
<td><img src="plumier.png" height="30px" onclick="showSendQuestion()"></td>
<td><img src="micro.png" height="30px"   onclick="showConnectControls()"></td>
<td><img src="QandA.png" height="30px"   onclick="showQuestions()"></td>
</tr>
</table>
</div>

</body>

</html>
