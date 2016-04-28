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
        <!--hide-->
        <!-- Styles used within the demo -->
        <style type="text/css">
            #demoContainer {
                position:relative;
            }
            #connectControls {
                float:left;
                width:250px;
                text-align:center;
                border: 2px solid black;
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
                <h1>Projet-X : server</h1>

                <!--show-->
                <div id="demoContainer">
                    <div id="connectControls">
                        <button id="hangupButton" disabled="disabled" onclick="hangup()">Fin Connection</button>
                        <div id="iam">Not yet connected...</div>
                        <br />
	                <strong>Connected user :</strong>
                        <div id="ConnectedClients"></div>
                        <br>
                        <strong>Waiting users:</strong>
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
