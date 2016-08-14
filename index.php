<?php 

// Retrieving required inputs
$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
$macAddr=$lines[3];
$numquestion = 0;
$remaining = 3 - $numquestion ;

if(isset($_GET['action']))
  $action=$_GET['action'] ;
else
  $action="N" ;

if(isset($_GET['conflist']))
  $conflist=$_GET['conflist'];
if(isset($_POST['conflist']))
  $conflist=$_POST['conflist'];

if(isset($_GET['name']))
  $name=htmlspecialchars($_GET['name'],ENT_HTML5);
if(isset($_POST['name']))
  $name=htmlspecialchars($_POST['name'],ENT_HTML5);
else
  {
    if ( isset($conflist) ) {
      // trying to recover name
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE macAddr = '$macAddr'";
      $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
      while($madata = mysqli_fetch_assoc($req))
        {
          $name = $madata['name'] ;
        }
      mysqli_close($db);
    }
  }


if ( isset($name) ) 
  {
    if ( isset($conflist) ) {
      // trying to recover name
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE macAddr = '$macAddr' AND question != ''";
      $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
      $row = mysqli_fetch_array($req2);
      $numquestion = $row[0];
      $remaining = 3 - $numquestion ;
      mysqli_close($db);
    }
  }

// Do we need to reset the question ?
//if (isset($_POST['resetquestion']))
//  {
//      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
//      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
//      $sql = "UPDATE ".$conflist." SET question='', votenum = '0', questime=curtime(), questove = '0'  WHERE macAddr='$macAddr'";   
//      mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
//      // remove all votes
//      $sql2 = "UPDATE ".$conflist." SET votefor = '' WHERE votefor='$macAddr'";
//      mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
//      mysqli_close($db);
//  }

// Do we need to register or read a question ?
if(isset($_POST['submitquestion']))    
  {
    if ( $remaining <= 0 )
      {

      }
    else
      {
        $yourquestion=$_POST['yourquestion'];
        $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
        mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
        $thequestion=mysqli_real_escape_string($db,$yourquestion) ;

        $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE macAddr = '".$macAddr."' AND question = ''";
        $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        $row2 = mysqli_fetch_array($req2);
        $count = $row2[0];
        if ( $count == 0 ) 
           {
             $sql = "INSERT INTO ".$conflist."(name,question, votenum, questime, macAddr, questove) 
                                   VALUES('$name','$thequestion','0',curtime(),'$macAddr','0')" ; 
           }
        else
           {
             $sql = "UPDATE ".$conflist." SET question='$thequestion', votenum = '0', questime=curtime(), questove = '0' WHERE macAddr='$macAddr' AND question=''";
           }
        $remaining = $remaining - 1 ;
        mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
        mysqli_close($db); 
      }
  }

// Do we want to disconnect ? 
if ($action=="D")
  {
    $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
    $sql = "UPDATE ".$conflist." SET isconnected = 0 , logout=now() WHERE macAddr='$macAddr'";   
    mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    mysqli_close($db);
    $name="";
  }

// Connection
if(isset($_POST['name']))
  {
     $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
     mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
     $thename= addcslashes(mysqli_real_escape_string($db,$name), '%_#') ;

     $sql2 = "SELECT COUNT(*) FROM ".$conflist." WHERE macAddr = '".$macAddr."'";
     $req2 = mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
     $row = mysqli_fetch_array($req2);
     $count = $row[0];
     if ( $count == 0 ) 
       {
         $sql = "INSERT INTO ".$conflist."(name, firstreg, isconnected, rtcid, macAddr,waitformic, question,login, logout) 
                                   VALUES('$thename', '1', '2','','$macAddr','','',now(),'')" ; 
       }
     else
       {
          $sql = "UPDATE ".$conflist." SET name = '$thename' , isconnected = '2', login = now() WHERE macAddr='$macAddr'";   
       }

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
               //if (document.getElementById("yourquestion").value == "")
               //  showSendQuestion();
               //else
                 showQuestions();
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
 
               ChangeStyle("connectedUsers_button") ;
               ResetStyle("sendQuestions_button") ;
               ResetStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
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

               ResetStyle("connectedUsers_button") ;
               ChangeStyle("sendQuestions_button") ;
               ResetStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
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

               ResetStyle("connectedUsers_button") ;
               ResetStyle("sendQuestions_button") ;
               ChangeStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
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

               ResetStyle("connectedUsers_button") ;
               ResetStyle("sendQuestions_button") ;
               ResetStyle("connectControls_button") ;
               ChangeStyle("questionList_button") ;
           }
      }
      window.onload = function () {
          toggleValue();
      }
      //window.addEventListener("orientationchange", function() {
        //document.location.reload(true);}, false) ;
        //alert(screen.orientation) ;}, false) ;
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
          #easyrtcErrorDialog {
             z-index:2;
             position:absolute;
             top:40px;
             margin: 0 auto ;
             width: 300px  ;
             border:red solid 2px;
             background-color:pink;
             padding:15px;
             font-size: 100%;
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
              padding-left: 10px;
              padding-top: 0px;
              text-align: center;
         }
         body>#menubottom {position:fixed}

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
         textarea {
              background-color : white ;
              color : black ;
              border-radius : 5px;
              font-family: "Times New Roman";
              font-size: 150%;
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

<body>

<div name="menutop" id="menutop">
<table>
<tr>
<td>Yona</td>
<td><?php if ((isset($name)) AND ($name != "")) echo "$name" ; else echo "NOKIA"; ?></td>
<td>
  <form name="byebye" id="byebye" method="post" 
    action="index.php?action=D&conflist=<?php if (isset($conflist)) echo $conflist?>">
     <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect" style="font-size: 50%;"
                   onClick="document.getElementById("name").value='';">
  </form>
</td>
</tr>
</table>
</div>

  <div name="main" id="main">
    <center>
    <form name="whoami" id="whoami" method="post" action="index.php?conflist=<?php if (isset($conflist)) echo $conflist?>"/>
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
      <input name="submitname" id="submitname" type="submit" value="Register" 
                                   style="color:white;background-color:#183693;font-size: 150%;" >
      <br><big><strong>Welcome to Yona<br>Please Register</strong></big>
    </form>
    </center>

    <div id="demoContainer">
        <div id="sendQuestions">
          <strong>Send your question by filling this form</strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Only three questions per user</li><li>Any question can be removed at any time</li></ul></i>
          <form name="question" id="question" method="post"  
            action="index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>" />
            <textarea style="width: 100%;height: auto;font-size: 100%;" maxlength="255" rows="5" placeholder="<?php echo $remaining." questions remaining" ?>" name="yourquestion" id="yourquestion"></textarea><br>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
          </form>
        </div>
        <div id="connectControls">
           <div style="text-align: left;">
           <strong>Ask for the micro by clicking the button</strong><br>
           <i>Rules: <ul style="margin-top: 0px;"><li>You will be asked for a confirmation before you can speak</li><li>Request can be removed at any time</li></ul></i>
           </div>
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
          <iframe style="border: none; height: 100%; width: 100%;" SCROLLING=auto 
             onload="javascript:ResizeIframe(this);"
             src="connected.php?name=<?php if (isset($name)) echo '$name'?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
        <div id="questionList">
          <iframe style="border: none; overflow: visible; width: 100%; height: 100%;" SCROLLING=auto 
             onload="javascript:ResizeIframe(this);"
             src="questions.php?name=<?php if (isset($name)) echo '$name'?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
    </div>
  </div>              

<div name="menubottom" id="menubottom">
<table width="100%">
<tr>
<td id="connectedUsers_button" name="connectedUsers_button"><img src="group.png" height="30px"   onclick="showUsers()"></td>
<td id="sendQuestions_button" name="sendQuestions_button"><img src="plumier.png" height="30px" onclick="showSendQuestion()"></td>
<td id="connectControls_button" name="connectControls_button"><img src="micro.png" height="30px"   onclick="showConnectControls()"></td>
<td id="questionList_button" name="questionList_button"><img src="QandA.png" height="30px"   onclick="showQuestions()"></td>
</tr>
</table>
</div>

</body>

</html>
