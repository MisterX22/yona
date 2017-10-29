<?php 

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
    $db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'))  or die('Erreur de connexion '.mysqli_connect_error());
    mysqli_select_db($db,getenv('MYSQL_DB'))  or die('Erreur de selection '.mysqli_error($db));
    $req = "SHOW TABLES LIKE '$name'" ;
    $res = mysqli_query($db,$req) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
    if(mysqli_num_rows($res) == 1)
      {
        if (isset($_POST['resetconf']))
          {
            if(isset($_POST['conflist']))
              $conflist=$_POST['conflist'];
            $imagetable=$conflist."_images" ;
            $sql = "DELETE FROM `$conflist`";   
            mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            $sql2 = "DELETE FROM `$imagetable`";   
            mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
          }
        if (isset($_POST['deleteconf']))
          {
            if(isset($_POST['conflist']))
               $conflist=$_POST['conflist'];
            $imagetable=$conflist."_images" ;
            $sql = "DROP TABLE `$conflist`";   
            mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            $sql2 = "DROP TABLE `$imagetable`";   
            mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
            $path="upload/".$name."/" ;
            $trash="trash/";
            rename($path,$trash) ;
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
        // creating directory for multimedia
        $path="upload/".$name."/" ;
        mkdir($path , 0755, true) ;
        $imagetable=$name."_images" ;
        $sql2 = "CREATE TABLE $imagetable ( 
                  id INT NOT NULL AUTO_INCREMENT, 
                  name VARCHAR(30),
                  path VARCHAR(255),
                  macAddr VARCHAR(30),
                  date DATETIME,
                  PRIMARY KEY (id)
              )";   
        mysqli_query($db,$sql2) or die('Erreur SQL !'.$sql2.'<br>'.mysqli_error($db));
        // creating directory for configuration
        $path="configuration/".$name."/" ;
        mkdir($path , 0755, true) ;
        $sessionopen="No" ;
        $file = $path."configuration.txt" ;
        file_put_contents($file, $sessionopen);
        // creating directory for trash
        $path="trash/".$name."/" ;
        mkdir($path , 0755, true) ;
      }
      mysqli_close($db);  
    }

$sessionopen="";
if ( $name != "" )
  {
    $config_path = "configuration/".$name."/" ;
    $file = $config_path."configuration.txt" ;
    $sessionopen=file_get_contents($file);
  }
if (isset($_POST['sessionopen']))
  {
    $sessionopen=$_POST['sessionopen'];
    $path="configuration/".$name."/" ;
    $file = $path."configuration.txt" ;
    file_put_contents($file, $sessionopen);
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
          $db = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'))  or die('Erreur de connexion '.mysqli_connect_error());
          mysqli_select_db($db,getenv('MYSQL_DB'))  or die('Erreur de selection '.mysqli_error($db));
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
            <option value='No' <?php if ($sessionopen == "No") echo "selected='selected';" ?> >No</option>
            <option value='Yes' <?php if ($sessionopen == "Yes") echo "selected='selected';" ?> >Yes</option>
        </select>
      <form>
      <br><br>
      <div id="connectControls">
        <div style="text-align: left;">
          <strong>Who wants to speak ?</strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Users requesting for the micro will appear</li><li>Give micro access by clicking on name</li><li>Access can be removed at any time by clicking on "Release it"</li></ul></i>
        </div>

        <center><button id="hangupButton" onclick="hangup()">Release it</button></center>
        <center><a href="" id="connect" onclick="connect(); return false;">Connect</a></center>
        <center><div id="iam">Not yet connected...</div></center>
        <br>
        <strong> <u>Waiting for Mic : </u></strong>
        <div id="ConnectedClients"></div>           
        <div id="otherClients"></div>
        <div style="text-align: left;" id="echo Cancellation">
          <h1>Echo Cancellation (voice detection)</h1>
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
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING=auto 
            src="./connected_master.php?name=Yona&conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>
	
   <div id="questionList">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING=auto 
            onload="javascript:ResizeIframe(this);"
            src="./questions_master.php?conflist=<?php if (isset($name)) echo $name?>">
        </iframe>
   </div>

   <div id="database">
        <iframe style="border: none; height: 100%; width: 100%;" SCROLLING=auto 
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
