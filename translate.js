//**********************************************************************************//
//    Copyright (c) Microsoft. All rights reserved.
//    
//    MIT License
//    
//    You may obtain a copy of the License at
//    http://opensource.org/licenses/MIT
//    
//    THE SOFTWARE IS PROVIDED AS IS, WITHOUT WARRANTY OF ANY KIND, 
//    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
//    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
//    IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
//    DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
//    OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE 
//    OR OTHER DEALINGS IN THE SOFTWARE.
//
//**********************************************************************************//
var filename ;
var lang ;
var langfrom ;
var langto ;
process.argv.forEach((val, index) => {
  if ( index == 2 ) {
    filename = val ;
  }
  if ( index == 3 ) {
    //lang = val ;
    langfrom = val ;
  }
  if ( index == 4 ) {
    langto = val ;
  }
});
var file='uploads/'.concat(filename) ;

var request = require('request');
var wsClient = require('websocket').client;
var fs = require('fs');
var streamBuffers = require('stream-buffers');
var SoundPlayer = require('soundplayer')
  
var player=new SoundPlayer();

var azureClientSecret = "5e918015f003445b89db4f5865064a10" ;
var speechTranslateUrl = 'wss://dev.microsofttranslator.com/speech/translate?api-version=1.0&from='.concat(langfrom,'&to=',langto,'&features=texttospeech');

// input wav file is in PCM 16bit, 16kHz, mono with proper WAV header
var outputAudioFile = 'translated/'.concat(filename) ;

// get Azure Cognitive Services Access Token for Translator APIs
request.post(
	{
		url: 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken',
		headers: {
			'Ocp-Apim-Subscription-Key': azureClientSecret
		},
		method: 'POST'
	},	
	// once we get the access token, we hook up the necessary websocket events for sending audio and processing the response
	function (error, response, body) {
		if (!error && response.statusCode == 200) {
			
			// get the acces token
			var accessToken = body;
			
			// connect to the speech translate api
			var ws = new wsClient();
			
			// event for connection failure
			ws.on('connectFailed', function (error) {
				console.log('Initial connection failed: ' + error.toString());
			});
									
			// event for connection succeed
			ws.on('connect', function (connection) {
				console.log('Websocket client connected');

				// process message that is returned
				connection.on('message', processMessage);
				
				connection.on('close', function (reasonCode, description) {
					console.log('Connection closed: ' + reasonCode);
				});

				// print out the error
				connection.on('error', function (error) {
					console.log('Connection error: ' + error.toString());
				});
				
				// send the file to the websocket endpoint
				sendData(connection, file);

   			});

			// connect to the service
			ws.connect(speechTranslateUrl, null, null, { 'Authorization' : 'Bearer ' + accessToken });
                        console.log(speechTranslateUrl) ;

		}
	}
);

// process the respond from the service
function processMessage(message) {
	if (message.type == 'utf8') {
		var result = JSON.parse(message.utf8Data)
                //fs.writeFile('translated/'.concat(file).'.txt', JSON.stringify(result) );
                fs.appendFile('translated/'.concat(filename,'.txt'), result.translation );
                fs.appendFile('translated/'.concat(filename,'.recog'), result.recognition );
		console.log('type:%s recognition:%s translation:%s', result.type, result.recognition, result.translation);
                console.log("azureKey: %s", process.env.azureKey) ;
	}
	else {
		// text to speech binary audio data if features=texttospeech is passed in the url
		// the format will be PCM 16bit 16kHz mono
		console.log("Receiving Data in %s", message.type);
                getAudioData(message);
	}
}

// load the file and send the data to the websocket connection in chunks
function sendData(connection, filename) {
	
	// the streambuffer will raise the 'data' event based on the frequency and chunksize
	var myReadableStreamBuffer = new streamBuffers.ReadableStreamBuffer({
		frequency: 100,   // in milliseconds. 
		chunkSize: 32000  // 32 bytes per millisecond for PCM 16 bit, 16 khz, mono.  So we are sending 1 second worth of audio every 100ms
	});
	
	// read the file and put it to the buffer
	myReadableStreamBuffer.put(fs.readFileSync(filename));
	
    // silence bytes.  If the audio file is too short after the user finished speeaking,
    // we need to add some silences at the end to tell the service that it is the end of the sentences
    // 32 bytes / ms, so 3200000 = 100 seconds of silences
	myReadableStreamBuffer.put(new Buffer(3200000));
	
	// no more data to send
	myReadableStreamBuffer.stop();
	
	// send data to underlying connection
	myReadableStreamBuffer.on('data', function (data) {
          connection.sendBytes(data);
	});

	myReadableStreamBuffer.on('end', function () {
          console.log('All data sent, closing connection');
	  connection.close(1000);
	});
}

// get data from the webSocket
function getAudioData(message) {
  var myReadableStreamBuffer = new streamBuffers.ReadableStreamBuffer({
    frequency: 100,
    chunkSize: 32000
  });
  var mymessage = JSON.stringify(message) ;
  var myparsed = JSON.parse(mymessage) ;
  var mybuffer = new Buffer.from(myparsed["binaryData"].data) ;
  var mydata = mybuffer ;
  myReadableStreamBuffer.put(mydata);
  myReadableStreamBuffer.stop();
  myReadableStreamBuffer.on('data', function (data) {
    fs.appendFile(outputAudioFile, data );
  });
  myReadableStreamBuffer.on('end', function () {
    console.log('All data receive, closing connection');
  });
}

