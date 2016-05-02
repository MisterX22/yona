//
//Copyright (c) 2015, Priologic Software Inc.
//All rights reserved.
//
//Redistribution and use in source and binary forms, with or without
//modification, are permitted provided that the following conditions are met:
//
//    * Redistributions of source code must retain the above copyright notice,
//      this list of conditions and the following disclaimer.
//    * Redistributions in binary form must reproduce the above copyright
//      notice, this list of conditions and the following disclaimer in the
//      documentation and/or other materials provided with the distribution.
//
//THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
//AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
//IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
//ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
//LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
//CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
//SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
//INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
//CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
//ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
//POSSIBILITY OF SUCH DAMAGE.
//
var selfEasyrtcid = "";

function disable(domId) {
    document.getElementById(domId).disabled = "disabled";
}


function enable(domId) {
    document.getElementById(domId).disabled = "";
}


function connect(name) {
    easyrtc.setSocketUrl(":8443");
    console.log("Initializing.");    
    easyrtc.enableVideo(false);
    easyrtc.enableVideoReceive(false);
    easyrtc.enableDataChannels(true);
    easyrtc.setUsername(name);
    easyrtc.setRoomOccupantListener(convertListToButtons);
    easyrtc.setPeerListener(addToConversation);
    easyrtc.initMediaSource(
        function(){        // success callback
            easyrtc.connect("easyrtc.audioOnly", loginSuccess, loginFailure);
        },
        function(errorCode, errmesg){
            easyrtc.showError(errorCode, errmesg);
        }  // failure callback
        );
}

function terminatePage() {
    easyrtc.disconnect();
}


function hangup() {
    easyrtc.hangupAll();
}


function clearConnectList() {
    nbClient = document.getElementById('nbClients');
    nbClient.innerHTML = "";
    document.getElementById("conversation").innerHTML = "" ;
}


function convertListToButtons (roomName, occupants, isPrimary) {
    clearConnectList();
    var nbCl=0;
    var TabName=[] ;
    for(var easyrtcid in occupants) {
        nbCl++;
	TabName.push(easyrtc.idToName(easyrtcid)) ;
    }
    TabName[0]= easyrtc.idToName(selfEasyrtcid) ;
    nbClient = document.getElementById('nbClients');
    nbClient.innerHTML = nbCl.toString() + " Questions<br>";
    for (var i=0;i<nbCl;i++) {
	nbClient.innerHTML += TabName[i] + "<br>" ;
    }
}

function addToConversation(who, msgType, content) {
  // Escape html special characters, then add linefeeds.
  content = content.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  content = content.replace(/\n/g, "<br />");
  document.getElementById("conversation").innerHTML = content ;
}


function performCall(otherEasyrtcid) {
    easyrtc.hangupAll();
    var acceptedCB = function(accepted, caller) {
        if( !accepted ) {
            easyrtc.showError("CALL-REJECTED", "Sorry, your call to " + easyrtc.idToName(caller) + " was rejected");
        }
    };
    var successCB = function() {
    };
    var failureCB = function() {
    };
    easyrtc.call(otherEasyrtcid, successCB, failureCB, acceptedCB);
}


function loginSuccess(easyrtcid) {
    disable("connectButton");
    enable("disconnectButton");
    selfEasyrtcid = easyrtcid;
    document.getElementById("iam").innerHTML = "Connected as "  + easyrtc.idToName(easyrtcid);
}


function loginFailure(errorCode, message) {
    easyrtc.showError(errorCode, message);
}


function disconnect() {
    document.getElementById("iam").innerHTML = "logged out";
    easyrtc.disconnect();
    console.log("disconnecting from server");
    enable("connectButton");
    disable("disconnectButton");
    clearConnectList();
}

function sendStuffWS(otherEasyrtcid, message) {
  if(message.replace(/\s/g, "").length === 0) { // Don"t send just whitespace
    return;
  }
  easyrtc.sendDataWS(otherEasyrtcid, "message", message);
}

easyrtc.setStreamAcceptor( function(easyrtcid, stream) {
    var audio = document.getElementById('callerAudio');
    easyrtc.setVideoObjectSrc(audio,stream);
});


easyrtc.setOnStreamClosed( function (easyrtcid) {
    easyrtc.setVideoObjectSrc(document.getElementById('callerAudio'), "");
});


easyrtc.setAcceptChecker(function(easyrtcid, callback) {
    document.getElementById('acceptCallBox').style.display = "block";
    if( easyrtc.getConnectionCount() > 0 ) {
        document.getElementById('acceptCallLabel').textContent = "Drop current call and accept new from " + easyrtc.idToName(easyrtcid) + " ?";
    }
    else {
	document.getElementById('acceptCallLabel').textContent = "You can now speak !!";
    }
    var acceptTheCall = function(wasAccepted) {
        document.getElementById('acceptCallBox').style.display = "none";
        if( wasAccepted && easyrtc.getConnectionCount() > 0 ) {
            easyrtc.hangupAll();
        }
        callback(wasAccepted);
    };
    document.getElementById("callAcceptButton").onclick = function() {
        acceptTheCall(true);
	document.getElementById("iam").innerHTML = "You are now speaking";
    };
    document.getElementById("callRejectButton").onclick =function() {
        acceptTheCall(false);
	disconnect();
    };
} );
