<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('includes/controller.php');
$controller = new Controller();
// Retrieving required inputs
//$ipclient=$_SERVER['HTTP_X_FORWARDED_FOR'];
$ipclient=$_SERVER['REMOTE_ADDR'];
$macAddr=false;
$arp=`arp -a $ipclient`;
$lines=explode(" ", $arp);
//$macAddr=$lines[3];
$macAddr=$ipclient;
$numquestion = 0;
$remaining = 3 - $numquestion ;
//$hostname=$lines[0] ;
$hostname="cloud9" ;

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
      $name = $controller->get_username_from_macAddr($conflist, $macAddr);
      if(!$name) {
        header("Refresh:0; url=./index.php");
      }
    }
  }

// How many remaining questions ?
if ( isset($name) ) 
  {
    if ( isset($conflist) ) {
      // trying to recover name
      $remaining = $controller->remaining_questions($conflist, $macAddr);
    }
  }

// Do we need to register or read a question ?
if(isset($_POST['submitquestion']))    
  {
    $yourquestion=$_POST['yourquestion'];
    $thequestion=$controller->escape($yourquestion);
    if($controller->post_question($conflist, $macAddr, $name, $thequestion)) {
      $remaining = $remaining - 1 ;
    }
    // we want to avoid double post on reload
    header('Location: ./index.php?conflist='.$conflist.'&name='.$name);
    exit;
  }

// Do we want to disconnect ? 
if ($action=="D")
  {
    $controller->logout_user($conflist, $macAddr);
    $name="";
  }

// Connection
if(isset($_POST['name']))
  {
    $thename= addcslashes($controller->escape($name), '%_#') ;
    $controller->register_user($conflist, $thename, $hostname, $macAddr);
  }
 
$uploadtext="Wants to try ? please dare !<br><br>" ;
if (isset($_FILES['uploadedimagefile']) && is_uploaded_file($_FILES["uploadedimagefile"]["tmp_name"]))
  {
    // get image data
    $binary = file_get_contents($_FILES['uploadedimagefile']['tmp_name']);

    // get mime type
    $finfo = new finfo(FILEINFO_MIME);
    $type = $finfo->file($_FILES['uploadedimagefile']['tmp_name']);
    $mime = substr($type, 0, strpos($type, ';'));

    $imagename=basename($_FILES['uploadedimagefile']['name']);
    $uploadtext="The file has been uploaded" ;
    $controller->save_image($conflist, $name, $macAddr, $imagename, $mime, $binary);
  }

if ( isset($conflist) )
  $sessionopen = $controller->is_session_open($conflist);
else
  $sessionopen = false ;

?>
   
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <!--skip-->
    <title>Audio Demo</title>
    <link rel="stylesheet" type="text/css" href="<?php print getenv("EASYRTC_SERVER"); ?>/easyrtc/easyrtc.css" />
    <meta name="viewport" content="width=device-width"/>

    <!--show-->
    <!-- Assumes global locations for socket.io.js and easyrtc.js -->
    <script src="<?php print getenv("EASYRTC_SERVER"); ?>/socket.io/socket.io.js"></script>
    <script type="text/javascript" src="<?php print getenv("EASYRTC_SERVER"); ?>/easyrtc/easyrtc.js"></script>
    <script type="text/javascript">
      function rtcServer() {
        return "<?php echo getenv("EASYRTC_SERVER") ?>";
      }
    </script>
    <script type="text/javascript" src="js/client.js"></script>

    <script type="text/javascript">
      function ResizeIframe(iframe)
        {
          var height = screen.height - 80 ;
          iframe.style.height = height + 'px' ;
        }

      function ResizeDiv(div)
        {
          var height = screen.height - 80 ;
          div.style.height = height + 'px' ;
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
               Hide("imageView");
               Disabling("unsubmitname");
            }
          else
            {
               Hide("whoami");
               <?php
                 if ( $page == '1' )
                   {
                     echo "showCamera();"; 
                   }
                 else 
                   {
                     echo "if (document.getElementById('yourquestion').placeholder != '0 questions remaining')";
                       echo "showSendQuestion();";
                     echo "else ";
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
               Hide("imageView");
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
               Hide("imageView");
 
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
               Hide("imageView");
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
               Hide("imageView");
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
               Hide("imageView");

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
               Hide("imageView");
           }
         else
           {
               Hide("whoami");
               Show("demoContainer");
               Hide("connectedUsers");
               Hide("camera");
               Hide("sendQuestions");
               <?php 
                 if ( $sessionopen )
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
               Hide("imageView");

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
               Hide("imageView");
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
               Hide("imageView");

               ResetStyle("camera_button") ;
               ResetStyle("sendQuestions_button") ;
               ResetStyle("connectControls_button") ;
               ChangeStyle("questionList_button") ;
           }
      }
      function afficheImage(id, conference) {
        Hide("camera");
        Show("imageView");
        document.getElementById("imageView").style.zIndex = "5";
        document.getElementById("imageView").style.backgroundImage = "url('image.php?id="+id+"&conflist="+conference+"')";
        document.getElementById("imageView").style.backgroundSize = "100%";
        document.getElementById("imageView").style.backgroundRepeat = "no-repeat";
        document.getElementById("imageView").style.backgroundPosition = "center";
        document.getElementById("imageView").style.border = "2px solid black" ;
        document.getElementById("imageView").style.borderRadius = "10px" ;
        ResizeDiv(document.getElementById("imageView")) ;
      }

      function ImageCollection(images) {
        this.images = images;
        this.i = 0;
        this.next = function(imgId) {
          var img = document.getElementById(imgId);
          this.i++;
          if (this.i == images.length )
            this.i = 0;
          afficheImage(images[this.i]) ;
        }
        this.prev = function(imgId) {
          var img = document.getElementById(imgId);
          this.i--;
          if (this.i < 0)
            this.i = images.length -1;
          afficheImage(images[this.i]) ;
        }
      }

      tab_java = new Array ;
<?php
  if (isset($conflist))
    {
      $a=0 ;
      foreach($controller->list_images($conflist) as $n)
        {
          echo "tab_java[$a] = '".$n['id']."';\n" ; 
          $a++ ;
        }
    }
?>
      var ic1 = new ImageCollection(tab_java) ;

      function cacheImage() {
        Hide("imageView");
        Show("camera");
      }

      window.onload = function () {
          toggleValue();
      }

    </script>
        
    <!-- Styles used within the demo -->
    <link rel="stylesheet" type="text/css" href="css/index.css">
</head>

<body style="font-family: 'Arial';">

  <div name="menutop" id="menutop">
    <table>
      <tr>
        <!--<td>Yona</td>-->
        <td><img src="images/yona.png" height="40px"></td>
        <td><?php if ((isset($name)) AND ($name != "")) echo "$name" ; else echo "Welcome"; ?></td>
        <td>
          <form name="byebye" id="byebye" method="post" 
            action="./index.php?action=D&conflist=<?php if (isset($conflist)) echo $conflist?>">
             <input name="unsubmitname" id="unsubmitname" type="submit" value="Disconnect" style="font-size: 50%;"
                           onClick="document.getElementById('name').value='';">
          </form>
        </td>
      </tr>
    </table>
  </div>

  <div name="main" id="main">
    <form name="whoami" id="whoami" method="post" action="./index.php?conflist=<?php if (isset($conflist)) echo $conflist?>"/>
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
        foreach($controller->list_conferences() as $table) {
            echo "<option value='".$table[0]."'>".$table[0]."</option> " ;
        }        
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
            action="./index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>" />
            <textarea style="width: 100%;height: auto;font-size: 100%;" maxlength="255" rows="5" 
                   placeholder="<?php echo $remaining." questions remaining" ?>"
                   name="yourquestion" id="yourquestion"></textarea><br>
            <input name="submitquestion" id="submitquestion" type="submit" value="Send">
          </form><br><br>
        </div>
        <div id="sessionnotopen">
          <h1>Sorry !</h1> 
          <strong>NO microphone access allowed yet</strong>
          <h3>Try Later !</h3>
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
          <iframe style="border: none; height: 100%; width: 100%;" SCROLLING="auto" 
             onload="javascript:ResizeIframe(this);"
             src="./connected.php?name=<?php if (isset($name)) echo '$name'?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
        <div id ="camera">
          <strong>Capture & post your images </strong><br>
          <i>Rules: <ul style="margin-top: 0px;"><li>Share your visuals !</li></ul></i>
          <?php echo $uploadtext ; ?>
          <form action="./index.php?name=<?php if (isset($name)) echo $name?>&conflist=<?php if (isset($conflist)) echo $conflist?>&P=1"  method="post" enctype="multipart/form-data"/>
            <input type="hidden" name="MAX_FILE_SIZE" value="41943004">
            <table style="width: 100%">
            <tr>
            <td>Take your picture !</td>
            <td><input type="file" name="uploadedimagefile" maxlength="41943004" accept="image/*" capture="camera" /></td>
            </tr>
            <tr>
            <td>And upload it</td>
            <td><input type="submit" value="Upload" /></td>
            <td></td> 
            </tr> 
            </table>
          </form>
          <br><hr><br>
          <form name="refresh" id="refresh" method="post" 
                 action="./index.php?conflist=<?php if (isset($conflist)) echo $conflist?>&P=1">
            <table>
            <tr><td>No automatic refresh<br>Please use button to refresh</td></tr>
            <tr><td><input type='button' value='Refresh' onclick='this.form.submit()'></td></tr>
            <tr><td>Full size within a click</td></tr>
            </table>
          </form>
          <?php
            if ( isset($conflist) )
            {
            $nb_fichier = 0;
            foreach($controller->list_images($conflist) as $data)
              {
                $id=$data['id'] ;
                //echo '<img height="50px" onClick="afficheImage(\''.$lepath.'\');" src="'.$lepath.'" alt="'.$nb_fichier.'" />&nbsp' ;
                echo '<img height="50px" onClick="afficheImage('.$id.', \''.$conflist.'\');" src="" alt="'.$nb_fichier.'" />&nbsp' ;
                $nb_fichier++;
              }
            echo '<br><strong>' . $nb_fichier .'</strong> files available';
            }
          ?>
        </div>

        <div id="imageView" name="imageView">
          <table style="width: 100%">
            <tr>
            <td><input type='button' value='<-- Prev' onclick='ic1.prev("imageView")' /></td>
            <td><input type='button' value='Close X' onclick='cacheImage();' /></td>
            <td><input type='button' value='Next -->' onclick='ic1.next("imageView")' /></td>
            </tr>
          </table>
        </div>
          
        <div id="questionList">
          <iframe style="border: none; overflow: visible; width: 100%; height: 100%;" SCROLLING="auto" 
             onload="javascript:ResizeIframe(this);"
             src="./questions.php?name=<?php if (isset($name)) echo '$name'?>&conflist=<?php if (isset($conflist)) echo $conflist?>&action=<?php if (isset($action)) echo $action ?>">
          </iframe>
        </div>
    </div>
  </div>              

  <div name="menubottom" id="menubottom">
    <table width="100%">
      <tr>
        <!--<td id="connectedUsers_button" name="connectedUsers_button"><img src="images/group.png" height="30px"   onclick="showUsers()"></td>-->
        <td id="camera_button" name="camera_button"><img src="images/camera.png" height="30px"   onclick="showCamera()"></td>
        <td id="sendQuestions_button" name="sendQuestions_button"><img src="images/plumier.png" height="30px" onclick="showSendQuestion()"></td>
        <td id="connectControls_button" name="connectControls_button"><img src="images/micro.png" height="30px"   onclick="showConnectControls()"></td>
        <td id="questionList_button" name="questionList_button"><img src="images/QandA.png" height="30px"   onclick="showQuestions()"></td>
      </tr>
    </table>
  </div>

</body>

</html>
