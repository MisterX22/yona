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
var currentCall = "" ;

var audio = null;
var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
var source = null;
var scriptNode = audioCtx.createScriptProcessor(512, 1, 1);
var pMax=0;

var voiceBegLevel=0.5; // threshold for voice begin
var voiceEndLevel=0.1;
var pQueue = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,]; // power of long period
var pAverage=0; // average power of long period
var maxP=0; //max power during voice period
var nbSamples=20;

var speaking=false;

var speakingDiv=null;

//------------------------------------------------------------------------------
//----------------- ECHO CANCELLATION ------------------------------------------
//------------------------------------------------------------------------------

var echoCancellationEnabled=false

scriptNode.onaudioprocess = function(audioProcessingEvent){

console.log("voiceBegLevel ",voiceBegLevel)

    // The input buffer is the song we loaded earlier
  var inputBuffer = audioProcessingEvent.inputBuffer;

  // The output buffer contains the samples that will be modified and played
  var outputBuffer = audioProcessingEvent.outputBuffer;

  // Loop through the output channels (in this case there is only one)
  var inputData = inputBuffer.getChannelData(0);
  var outputData = outputBuffer.getChannelData(0);

  var p=0;
  // Loop through the samples
  for (var sample = 0; sample < inputBuffer.length; sample++) {
    // make output equal to the same as the input
    p += inputData[sample]*inputData[sample];
    outputData[sample] = 0;
  }
  pAverage=pAverage-pQueue.shift()+p/nbSamples;



  pQueue.push(p/nbSamples);
  
  if (speaking==false){
    if (p>(pAverage+voiceBegLevel)){
      speaking=true;
      maxP=0;
      console.log('speaking : ',speaking);
      audio.muted = false;
      speakingDiv.innerHTML="Voice status : speaking";
    }
  }
  else
  {
    if (pAverage>maxP) maxP=pAverage; 
    if (pAverage<(maxP*voiceEndLevel)){
      speaking=false;
      console.log('speaking : ',speaking);
      audio.muted = true;
      speakingDiv.innerHTML="Voice status : silence";
    }
  }

}

function enableEchoCancellation(event) {
    if (event) {
        console.log('echo cancellation enabled');
        echoCancellationEnabled=true;
    } else {
        console.log('echo cancellation disabled');
        echoCancellationEnabled=false;
    }
};

//------------------------------------------------------------------------------
//-----------------  END ECHO CANCELLATION -------------------------------------
//------------------------------------------------------------------------------











function disable(domId) {
    document.getElementById(domId).disabled = "disabled";
}


function enable(domId) {
    document.getElementById(domId).disabled = "";
}


function connect() {
    //easyrtc.setSocketUrl(":8443");
    easyrtc.setSocketUrl("https://yona-misterx22.c9users.io:8081");
    console.log("Initializing.");
    easyrtc.enableVideo(false);
    easyrtc.enableVideoReceive(false);
    easyrtc.enableAudio(false);
    easyrtc.enableAudioReceive(true);
    easyrtc.enableDataChannels(true);
    easyrtc.setRoomOccupantListener(convertListToButtons);
    easyrtc.setPeerListener(addToConversation);
    easyrtc.setUsername("Yona");
    easyrtc.connect("easyrtc.audioOnly", loginSuccess, loginFailure);
//    easyrtc.initMediaSource(
//        function(){        // success callback
//            easyrtc.connect("easyrtc.audioOnly", loginSuccess, loginFailure);
//        },
//        function(errorCode, errmesg){
//            easyrtc.showError(errorCode, errmesg);
//        }  // failure callback
//        );
}

function terminatePage() {
    easyrtc.disconnect();
}


function hangup() {
    easyrtc.hangupAll();
    disable('hangupButton');
    clearCallList();
}

function addToConversation(who, msgType, content) {
  // Escape html special characters, then add linefeeds.
  content = content.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
  content = content.replace(/\n/g, "<br />");
  document.getElementById("conversation").innerHTML +=
  "<b>" + who + ":</b>&nbsp;" + content + "<br />";
}

function clearConnectList() {
    otherClientDiv = document.getElementById('otherClients');
    while (otherClientDiv.hasChildNodes()) {
        otherClientDiv.removeChild(otherClientDiv.lastChild);
    }
}

function clearCallList() {
    otherClientDiv = document.getElementById('ConnectedClients');
    while (otherClientDiv.hasChildNodes()) {
        otherClientDiv.removeChild(otherClientDiv.lastChild);
    }
}

function convertListToButtons (roomName, occupants, isPrimary) {
    clearConnectList();
    clearCallList();
    var otherClientDiv = document.getElementById('otherClients');
    for(var easyrtcid in occupants) {
        var button = document.createElement('button');
        button.onclick = function(easyrtcid) {
            return function() {
                performCall(easyrtcid);
		currentCall = easyrtc.idToName(easyrtcid) ;
                var theDiv = document.getElementById("ConnectedClients");
		 while (theDiv.hasChildNodes()) {
                   theDiv.removeChild(theDiv.lastChild);
                 }
                var content = document.createTextNode(currentCall);
                theDiv.appendChild(content);
		var theoccupants = easyrtc.getRoomOccupantsAsArray(roomName);
                var i;
		var j, nameArray=[] ;
		for( j = 0; j < theoccupants.length; j++ ) {
                  nameArray.push(easyrtc.idToName(j)) ;
                }
                for( i = 0; i < theoccupants.length ; i++ ) {
                  lemessage =  currentCall + " is now speaking" ;
		  if ( theoccupants[i] != selfEasyrtcid ) {
                      sendStuffWS(theoccupants[i], lemessage ) ;
		  }
                }
            };
        }(easyrtcid);

        var label = document.createElement('text');
        label.innerHTML = easyrtc.idToName(easyrtcid);
        button.appendChild(label);
        otherClientDiv.appendChild(button);
    }
}

function sendStuffWS(otherEasyrtcid, message) {
  if(message.replace(/\s/g, "").length === 0) { // Don"t send just whitespace
    return;
  }
  easyrtc.sendDataWS(otherEasyrtcid, "message", message);
  //addToConversation("Me", "message", message);
}

function performCall(otherEasyrtcid) {
    easyrtc.hangupAll();
    var acceptedCB = function(accepted, caller) {
        if( !accepted ) {
            easyrtc.showError("CALL-REJECTED", "Sorry, your call to " + easyrtc.idToName(caller) + " was rejected");
            enable('otherClients');
        }
    };
    var successCB = function() {
        enable('hangupButton');
    };
    var failureCB = function() {
        enable('otherClients');
    };
    easyrtc.call(otherEasyrtcid, successCB, failureCB, acceptedCB);
    //sendtoAll("is speaking") ;
    //sendStuffWS(otherEasyrtcid, "ben alors !") ;
}

function loginSuccess(easyrtcid) {
    enable('otherClients');
    selfEasyrtcid = easyrtcid;
    document.getElementById("iam").innerHTML = "I am " + easyrtc.idToName(easyrtcid);
}


function loginFailure(errorCode, message) {
    easyrtc.showError(errorCode, message);
}


function disconnect() {
    document.getElementById("iam").innerHTML = "logged out";
    easyrtc.disconnect();
    console.log("disconnecting from server");
    enable("connectButton");
    // disable("disconnectButton");
    clearConnectList();
}

easyrtc.setStreamAcceptor( function(easyrtcid, stream) {

    audio = document.getElementById('callerAudio');
    speakingDiv= document.getElementById('speaking');

    voiceBegLevel=parseFloat(document.getElementById('echo_cancellation_begin_threshold').value);
    voiceEndLevel=parseFloat(document.getElementById('echo_cancellation_end_threshold').value);

    easyrtc.setVideoObjectSrc(audio,stream);
    enable("hangupButton");
    
    if (echoCancellationEnabled){
        audio.muted = true;
        speaking=false;
        speakingDiv.innerHTML="Voice status : silence";
        source = audioCtx.createMediaStreamSource(stream);
        source.connect(scriptNode);
        scriptNode.connect(audioCtx.destination);
    }
    else{
        audio.muted = false;
    }
});


easyrtc.setOnStreamClosed( function (easyrtcid) {
    easyrtc.setVideoObjectSrc(document.getElementById('callerAudio'), "");
    disable("hangupButton");
    if (source != null){
        source.disconnect(scriptNode);
        scriptNode.disconnect(audioCtx.destination);
        }
    speakingDiv.innerHTML="Voice status : unknown";


});


easyrtc.setAcceptChecker(function(easyrtcid, callback) {
    document.getElementById('acceptCallBox').style.display = "block";
    if( easyrtc.getConnectionCount() > 0 ) {
        document.getElementById('acceptCallLabel').textContent = "Drop current call and accept new from " +  easyrtc.idToName(easyrtcid) + " ?";
    }
    else {
        document.getElementById('acceptCallLabel').textContent = "Accept incoming call from " +  easyrtc.idToName(easyrtcid) + " ?";
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
    };
    document.getElementById("callRejectButton").onclick =function() {
        acceptTheCall(false);
    };
} );
