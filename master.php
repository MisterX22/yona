<?php 
include('includes/controller.php');
$controller = new Controller();

// Retrieving all required inputs
//if(isset($_GET['name']))
//  $name = $_GET['name'] ;
if(isset($_GET['conflist']))
  $name = $_GET['conflist'] ;

if(isset($_POST['name']))
  {
    $name=$_POST['name'];
    if ($name == "")
      if(isset($_POST['conflist']))
        $name=$_POST['conflist'];
  }
else
  {
    if(isset($_POST['conflist']))
      $name=$_POST['conflist'];
  }

if(isset($_GET['action']))
  $action=$_GET['action'] ;
else
  $action="N" ;

// Do we want to disconnect ? 
if ($action=="D")
  {
    $name="";
  }

// Do we want to shutdown ? 
if ($action=="S")
  {
    $name="";
    $shutdown=`sudo shutdown now`;
  }

// Managing table if required
if(empty($name))
  {
    // do nothing
  }
else
  {
    if($controller->conference_exists($name))
      {
        if(isset($_POST['conflist']))
          $conflist=$_POST['conflist'];
        if (isset($_POST['resetconf']))
        {
          $controller->reset_conference($conflist);
        }
        if (isset($_POST['deleteconf']))
        {
          $controller->delete_conference($conflist);
          $name = "";
        } 
      }
    else
    {
      $controller->create_conference($name);
    }
  }

$sessionopen=false;
if ( $name != "" )
  $sessionopen=$controller->is_session_open($name);
if (isset($_POST['sessionopen']))
  {
    $sessionopen = $_POST['sessionopen'] == "Yes";
    $controller->set_session($name, $sessionopen);
  }

?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <!--skip-->
        <title>Audio Demo</title>
        <link rel="stylesheet" type="text/css" href="<?php print getenv("EASYRTC_SERVER"); ?>/easyrtc/easyrtc.css" />

        <!--show-->
        <!-- Assumes global locations for socket.io.js and easyrtc.js -->
        <script src="<?php print getenv("EASYRTC_SERVER"); ?>/socket.io/socket.io.js"></script>
        <script type="text/javascript" src="<?php print getenv("EASYRTC_SERVER"); ?>/easyrtc/easyrtc.js"></script>
<script src="https://cdn.webrtc-experiment.com/RecordRTC.js"></script>
        <script type="text/javascript">
          function rtcServer() {
            return "<?php echo getenv("EASYRTC_SERVER") ?>";
          }
        </script>
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
      function showconnectControls() {
        if (document.getElementById("sessionopen").value == "Yes")
           {
              Show("connectControls");
           }
        else
           {
              Hide("connectControls");
           }
      }
      function toggleValue() {
          if (document.getElementById("name").value == "")
            {
               Show("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
               Disabling("unsubmitname");
               Disabling("shutdown");
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
               Hide("database");
           }
         else
           {
               Hide("conf");
               Hide("demoContainer");
               Show("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
               Hide("database");

               ChangeStyle("connectedUsers_button") ;
               ResetStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
               ResetStyle("database_button") ;
           }
      }
      function showDatabase() {
         if (document.getElementById("name").value == "")
           {
               Show("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
               Hide("database");
           }
         else
           {
               Hide("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Hide("questionList");
               Show("database");

               ChangeStyle("database_button") ;
               ResetStyle("connectedUsers_button") ;
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
               Hide("database");
           }
         else
           {
               Hide("conf");
               Show("demoContainer");
               Hide("connectedUsers");
               showconnectControls() ;
               Hide("questionList");
               Hide("database");

               ResetStyle("connectedUsers_button") ;
               ChangeStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
               ResetStyle("database_button") ;
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
               Hide("database");
           }
         else
           {
               Hide("conf");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("connectControls");
               Show("questionList");
               Hide("database");

               ResetStyle("connectedUsers_button") ;
               ResetStyle("connectControls_button") ;
               ChangeStyle("questionList_button") ;
               ResetStyle("database_button") ;
           }
      }
      window.onload = function () {
          toggleValue();
      };
    </script>
    <link rel="stylesheet" type="text/css" href="css/master.css">
  </head>
<!--  <body style="font-family: 'Arial';" onload="setTimeout('connect()',4000)">-->
  <body style="font-family: 'Arial';">
	
  <div name="menutop" id="menutop">
    <table width="100%">
    <tr>
    <td>Yona</td>
    <td><?php if ((isset($name)) AND ($name != "")) echo "$name" ; else echo "NOKIA"; ?></td>
    <td>
      <form name="byebye" id="byebye" method="post" 
          action="./master.php?action=D&name=<?php if (isset($name)) echo $name?>">
        <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect" style="font-size: 50%;"
            onClick="document.getElementById("name").value='';">
      </form>
    </td>
    <td>
      <form name="shutdown" id="shutdown" method="post" 
          action="./master.php?action=S&name=<?php if (isset($name)) echo $name?>">
        <input name="unsubmitname" id="unsubmitname" type="submit" value="Shutdown" style="font-size: 50%;"
            onClick="document.getElementById("name").value='';">
      </form>
    </td>
    </tr>
    </table>
  </div>
  
  <div id="main">
    <!-- Main Content -->
    <form name="conf" id="conf" method="post" action="./master.php"/>
      <table>
      <tr>
      <td><big><strong>Existing Conference : </strong></big></td>
      <td><select name="conflist" id="conflist" style="font-size: 150%">
        <?php
          // list conference
          foreach($controller->list_conferences() as $table) {
              echo "<option value='".$table[0]."'>".$table[0]."</option> " ;
          }   
        ?>
      </select></td> 
      <td><input name="connectconf" id="connectconf" type="submit" value="ConnectConf"></td> 
      <td><input name="resetconf" id="resetconf" type="submit" value="ResetConf"></td> 
      <td><input name="deleteconf" id="deleteconf" type="submit" value="DeleteConf"></td>
      </tr>
      <tr>
      <td><big><strong>Conference Name:</strong></big></td>
      <!--<td><form name="createconf" id="createconf" method="post" action="./master.php" style="font-size: 150%"/>!-->
      <td><input type="text" name="name" id="name" value="<?php if (isset($name)) echo $name;?>"></td>
      <td><input name="submitname" id="submitname" type="submit" value="Create"></td>
      <!--</form></td>!-->
      </tr>
      </table>
    </form>

    <br>
	
    <!--show-->
    <div id="demoContainer">
      <br>
      <form name="openMicro" id ="openMicro" method="post" action="./master.php?name=Yona&conflist=<?php if (isset($name)) echo $name?>">
        Microphone sessions : 
        <select name="sessionopen" id="sessionopen" onChange="this.form.submit()">
            <option value='No' <?php if ($sessionopen) echo "selected='selected';" ?> >No</option>
            <option value='Yes' <?php if ($sessionopen) echo "selected='selected';" ?> >Yes</option>
        </select>
      </form>
      <br><br>
      <div id="connectControls">
        <div style="text-align: left;">
          <strong>Who wants to speak ?</strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Users requesting for the micro will appear</li><li>Give micro access by clicking on name</li><li>Access can be removed at any time by clicking on "Release it"</li></ul></i>
        </div>

        <center><button id="hangupButton" disabled="disabled" onclick="return hangup();">Release it</button></center>
        <center><a href="javascript:void(0);" id="connect" onclick="return connect();">Connect</a></center>
        <center><div id="iam">Not yet connected...</div></center>
        <br>
        <strong> <u>Waiting for Mic : </u></strong>
        <div id="ConnectedClients"></div><div id="otherClients"></div>
        <hr>
        <div style="text-align: left;" id="Traduction">
          <strong>Traduction On</strong>
          <label><input type="checkbox" id="traduction_enable" value="traduction_enabled"> enable traduction</label>
          From :
          <select id="langfrom" name="langfrom">
            <option value="en-US">en-US</option>
            <option value="fr-FR">fr-FR</option>
            <option value="de-DE">de-DE</option>
          </select>
          To :
          <select id="langto" name="langto">
            <option value="en-US">en-US</option>
            <option value="fr-FR">fr-FR</option>
            <option value="de-DE">de-DE</option>
          </select>
        </div>
        <hr>
        <div style="text-align: left;" id="echo Cancellation">
          <strong>Echo Cancellation (voice detection)</strong>
          <div id="speaking">Voice status : unknown</div>
          <label><input type="checkbox" id="echo_cancellation_enable" value="echo_enabled" onclick="enableEchoCancellation(this.checked);"> enable echo cancellation</label>
          <br>begin Threshold : <input type="number" name="nombre" value="0.5" step="0.05" id="echo_cancellation_begin_threshold">
          <br>end Threshold : <input type="number" name="nombre" value="0.1" step="0.01"id="echo_cancellation_end_threshold">
        </div>
		
        <!-- Note... this demo should be updated to remove video references -->
        <div id="videos">
            <audio id="callerAudio"></audio>
            <div id="acceptCallBox">
                <div id="acceptCallLabel"></div>
                <br><br>
                <button id="callAcceptButton" >Accept</button> or <button id="callRejectButton">Reject</button>
            </div>
        </div>
      </div>
   </div>
	
   <div id="connectedUsers">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING="auto" 
            src="./connected_master.php?name=Yona&conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>
	
   <div id="questionList">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING="auto" 
            onload="javascript:ResizeIframe(this);"
            src="./questions_master.php?conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>

   <div id="database">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING="auto" 
            onload="javascript:ResizeIframe(this);"
            src="./database.php?name=Yona&conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>
	
  </div>
   <div name="menubottom" id="menubottom">
      <table width="100%">
        <tr>
          <td id="connectedUsers_button" name="connectedUsers_button"><img src="images/group.png" height="30px"   onclick="showUsers()"></td>
          <td id="database_button" name="database_button"><img src="images/db.png" height="30px"   onclick="showDatabase()"></td>
          <td id="connectControls_button" name="connectControls_button"><img src="images/micro.png" height="30px"   onclick="showConnectControls()"></td>
          <td id="questionList_button" name="questionList_button"><img src="images/QandA.png" height="30px"   onclick="showQuestions()"></td>
        </tr>
      </table>
  </div>

</body>
</html>
