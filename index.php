<?php 

// Retrieving required inputs
$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
$macAddr=$lines[3];
$numquestion = 0;
$remaining = 3 - $numquestion ;
$hostname=$lines[0] ;

// What action ?
if(isset($_GET['action']))
  $action=$_GET['action'] ;
else
  $action="N" ;

$page='0';
if(isset($_GET['P']))
   $page='1';

// Which conference ?
if(isset($_GET['conflist']))
  $conflist=$_GET['conflist'];
if(isset($_POST['conflist']))
  $conflist=$_POST['conflist'];

// Which name ?
if(isset($_GET['name']))
  $name=htmlspecialchars($_GET['name'],ENT_HTML5);
if(isset($_POST['name']))
  $name=htmlspecialchars($_POST['name'],ENT_HTML5);
else
  {
    if ( isset($conflist)and ($conflist != "") ) {
      // trying to recover name
      $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
      mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
      $sql = "SELECT name FROM ".$conflist." WHERE macAddr = '$macAddr'";
      //$req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db)) ;
      $req = mysqli_query($db,$sql) or header("Refresh:0; url=https://192.168.2.1/index.php");
      while($madata = mysqli_fetch_assoc($req))
        {
          $name = $madata['name'] ;
        }
      mysqli_close($db);
    }
  }

// How many remaining questions ?
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

// Do we need to register or read a question ?
if(isset($_POST['submitquestion']))    
  {
    if ( $remaining <= 0 )
      {
         // Do nothing
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
        // we want to avoid double post on reload
        header('Location: https://192.168.2.1/index.php?conflist='.$conflist.'&name='.$name);
        exit;
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
         $sql = "INSERT INTO ".$conflist."(name, hostname, firstreg, isconnected, rtcid, macAddr,waitformic, question,login, logout) 
                                   VALUES('$thename', '$hostname', '1', '2','','$macAddr','','',now(),'')" ; 
       }
     else
       {
          $sql = "UPDATE ".$conflist." SET hostname = '$hostname', name = '$thename' , isconnected = '2', login = now() WHERE macAddr='$macAddr'";   
       }

     mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
     mysqli_close($db); 
  }

$target_path = "upload/".$conflist."/" ;
$uploadtext="Wants to try ? please dare !<br><br>" ;
if (isset($_FILES['uploadedimagefile']))
  {
    if(is_uploaded_file($_FILES["uploadedimagefile"]["tmp_name"]))
      {
        $imagename=basename($_FILES['uploadedimagefile']['name']);
        $target_path = $target_path.$name."_".$imagename ;
        if (move_uploaded_file($_FILES["uploadedimagefile"]["tmp_name"], $target_path))
          {
            $uploadtext="The file has been uploaded" ;
            $imagetable=$conflist."_images" ;
            $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
            mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
            $sql = "INSERT INTO ".$imagetable."(name,path,macAddr,date) 
                                   VALUES('$name', '$target_path', '$macAddr',now())" ; 
            mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            mysqli_close($db); 
          }
      }
  }


$file = "configuration.txt" ;
$sessionopen=file_get_contents($file);

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
          //document.getElementById(addr).style.borderTop="thick solid grey";
          document.getElementById(addr).style.borderTop="solid 1px grey";
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
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
               Disabling("unsubmitname");
            }
          else
            {
               Hide("whoami");
               //if (document.getElementById("yourquestion").value == "")
               <?php
                 if ( $page == '1' )
                   {
                     echo "showCamera();"; 
                   }
                 else 
                   {
                     echo "if (document.getElementById('yourquestion').placeholder != '0 questions remaining')";
                       echo "showSendQuestion();";
                     echo "else";
                       echo "showQuestions();";
                   }
               ?>
            }
      }
      function showUsers() {
         if (document.getElementById("name").value == "")
           {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Show("connectedUsers");
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
 
               //ChangeStyle("connectedUsers_button") ;
               ChangeStyle("camera_button") ;
               ResetStyle("sendQuestions_button") ;
               ResetStyle("connectControls_button") ;
               ResetStyle("questionList_button") ;
           }
      }
      function showCamera() {
         if (document.getElementById("name").value == "")
           {
               Show("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Show("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
 
               ChangeStyle("camera_button") ;
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
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("camera");
               Show("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");

               //ResetStyle("connectedUsers_button") ;
               ResetStyle("camera_button") ;
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
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Show("demoContainer");
               Hide("connectedUsers");
               Hide("camera");
               Hide("sendQuestions");
               <?php 
                 if ( $sessionopen == "Yes" )
                   {
                      echo "Show('connectControls');" ;
                      echo "Hide('sessionnotopen');" ;
                   }
                 else
                   {
                      echo "Hide('connectControls');" ;
                      echo "Show('sessionnotopen');" ;
                   }
               ?>
               Hide("questionList");

               //ResetStyle("connectedUsers_button") ;
               ResetStyle("camera_button") ;
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
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Hide("questionList");
           }
         else
           {
               Hide("whoami");
               Hide("demoContainer");
               Hide("connectedUsers");
               Hide("camera");
               Hide("sendQuestions");
               Hide("connectControls");
               Hide("sessionnotopen");
               Show("questionList");

               //ResetStyle("connectedUsers_button") ;
               ResetStyle("camera_button") ;
               ResetStyle("sendQuestions_button") ;
               ResetStyle("connectControls_button") ;
               ChangeStyle("questionList_button") ;
           }
      }
      window.onload = function () {
          toggleValue();
      }
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

         #whoami { 
              visibility: hidden;
              position: absolute;
              top: 40px;
              left: 0px;
              width: 400px%;
              height: 100%;
         }
 
         #sendQuestions, #connectedUsers, #camera, #connectControls, #questionList, #sessionnotopen {
              visibility: hidden;
              position: absolute;
              top: 40px;
              left: 0px;
              width: 100%;
              height: 100%;
         }
 
         #rules {
              font-style : italic ;
              //position: absolute;
              //top: 200px;
              //left: 5px;
              margin-top: 0px;
              margin-bottom: 0px;
              padding-top: 0px;
              padding-bottom: 0px;
         }

         input[type=button], input[type=submit], input[type=reset], button {
              -webkit-appearance: none;
              background-color : #183693 ;
              color : white ;
              border-radius : 5px;
              font-size: 100%;
         }
         textarea {
              background-color : white ;
              color : black ;
              border-radius : 5px;
              font-family: "Arial";
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

<body style="font-family: 'Arial';">

  <div name="menutop" id="menutop">
    <table>
      <tr>
        <!--<td>Yona</td>-->
        <td><img src="yona.png" height="40px"></td>
        <td><?php if ((isset($name)) AND ($name != "")) echo "$name" ; else echo "Welcome"; ?></td>
        <td>
          <form name="byebye" id="byebye" method="post" 
            action="https://192.168.2.1/index.php?action=D&conflist=<?php if (isset($conflist)) echo $conflist?>">
             <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect" style="font-size: 50%;"
                           onClick="document.getElementById("name").value='';">
          </form>
        </td>
      </tr>
    </table>
  </div>

  <div name="main" id="main">
    <form name="whoami" id="whoami" method="post" action="https://192.168.2.1/index.php?conflist=<?php if (isset($conflist)) echo $conflist?>"/>
    <center>
      <table>
      <tr>
      <td>Name : <?php if (isset($name)) echo "$name" ?></td>
      <td><input type="text" placeholder="Name" name="name" id="name" maxlength="20" style="font-size: 100%; width:200px;" 
                                      value="<?php if (isset($name)) echo $name; ?>"></td>
      </tr>
      <tr>
      <td style="font-style: italic">Example: </td><td style="font-style: italic">John/MN/CC/CSDM</td>
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
         while($table = mysqli_fetch_array($req))
           {
             echo "<option value='".$table[0]."'>".$table[0]."</option> " ;
           }
         mysqli_close($db);        
      ?>
      </select></td>
      </tr>
      </table>
      <input name="submitname" id="submitname" type="submit" value="Register" 
                                   style="color:white;background-color:#183693;font-size: 150%;" >
      <br><big><strong>Welcome to Yona<br>Please register & let's play</strong></big>
    </center>
    <br><div id="rules" name="rules" >
      Rules : <br>&nbsp;&nbsp;&nbsp;Each player can : 
      <ul style="margin-top: 0px;">
        <li>post up to 3 questions</li>
        <li>see all questions</li>
        <li>grant up to 3 points</li>
        <li>remove owned questions / points</li>
     </ul>
    </div>
    </form>

    <div id="demoContainer">
        <div id="sendQuestions">
          <strong>Send your question by filling this form</strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Only three questions per user</li><li>Owned question can be removed (see Q&A tab)</li></ul></i>
          <form name="question" id="question" method="post"  
            action="https://192.168.2.1/index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>" />
            <textarea style="width: 100%;height: auto;font-size: 100%;" maxlength="255" rows="5" 
                   placeholder="<?php echo $remaining." questions remaining" ?>"
                   name="yourquestion" id="yourquestion"></textarea><br>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
          </form><br><br>
        </div>
        <div id="sessionnotopen">
          <h1>Sorry no micro allowed for this session</h1>
        </div>
        <div id="connectControls">
           <div style="text-align: left;">
             <strong>Ask for the micro by clicking the button</strong><br>
             <i>Rules:  
             <ul style="margin-top: 0px;">
               <li>You will be asked for a confirmation before you can speak</li>
               <li>Request can be removed at any time</li>
             </ul>
             </i>
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
             src="https://192.168.2.1/connected.php?name=<?php if (isset($name)) echo '$name'?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
        <div id ="camera">
          <strong>Capture & post your images </strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Share your visuals !</li><li>Click & upload it</li></ul></i>
          <?php echo $uploadtext ; ?>
          <form action="https://192.168.2.1/index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>&P=1"  method="post" enctype="multipart/form-data"/>
            <input type="hidden" name="MAX_FILE_SIZE" value="41943004">
            <table>
            <tr>
            <td>Click it !</td>
            <td><input type="file" name="uploadedimagefile" maxlength="41943004" accept="image/*" capture="camera" /></td>
            </tr>
            <tr>
            <td><input type="submit" value="Upload" /></td>
            <td></td> 
            </tr> 
            </table>
          </form>
          <br><br>
          <form name="refresh" id="refresh" method="post" 
                 action="https://192.168.2.1/index.php?conflist=<?php if (isset($conflist)) echo $conflist?>&P=1">
            <table>
            <tr><td>No automatic refresh please use button to see new images</td></tr>
            <tr><td><input type='button' value='Refresh' onclick='this.form.submit()'></td></tr>
            </table>
          </form>
          <?php
            $nb_fichier = 0;
            $imagetable=$conflist."_images" ;
            $db = mysqli_connect('localhost', 'root', 'jojo0108')  or die('Erreur de connexion '.mysqli_connect_error());
            mysqli_select_db($db,'projectX')  or die('Erreur de selection '.mysqli_error($db));
            $sql = "SELECT name, path FROM ".$imagetable ;
            $req = mysqli_query($db,$sql) or die('Erreur SQL !'.$sql.'<br>'.mysqli_error($db));
            while($data = mysqli_fetch_assoc($req))
              {
                $name=$data['name'] ;
                $path=$data['path'] ;
                if (file_exists($path))
                  {
                    echo '<a target="_blank" href="'.$path.'"><img height="50px" src="'.$path.'"/></a>&nbsp' ;
                    $nb_fichier++;
                  }
              }
            echo '<br><strong>' . $nb_fichier .'</strong> files available';
            mysqli_close($db);
          ?>
        </div>
        <div id="questionList">
          <iframe style="border: none; overflow: visible; width: 100%; height: 100%;" SCROLLING=auto 
             onload="javascript:ResizeIframe(this);"
             src="https://192.168.2.1/questions.php?name=<?php if (isset($name)) echo '$name'?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
    </div>
  </div>              

  <div name="menubottom" id="menubottom">
    <table width="100%">
      <tr>
        <!--<td id="connectedUsers_button" name="connectedUsers_button"><img src="group.png" height="30px"   onclick="showUsers()"></td>-->
        <td id="camera_button" name="camera_button"><img src="camera.png" height="30px"   onclick="showCamera()"></td>
        <td id="sendQuestions_button" name="sendQuestions_button"><img src="plumier.png" height="30px" onclick="showSendQuestion()"></td>
        <td id="connectControls_button" name="connectControls_button"><img src="micro.png" height="30px"   onclick="showConnectControls()"></td>
        <td id="questionList_button" name="questionList_button"><img src="QandA.png" height="30px"   onclick="showQuestions()"></td>
      </tr>
    </table>
  </div>

</body>

</html>
