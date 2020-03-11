import { OpenVidu } from "openvidu-browser";

export function OpenViduManager() {
    var OV = new OpenVidu();
    var session;
	
    var manager = {
        subscriber:null,
        publisher: null,
        joinSession(sessionId, data, callbacks) {
            return new Promise((resolve, reject) => {
                var mySessionId = sessionId || document.getElementById("sessionId").value;
                session = OV.initSession();        
                
                session.on("streamCreated", (event)=> {
                    this.subscriber = session.subscribe(event.stream, "remoteVideo", {
                        insertMode: 'APPEND'
                    });
                    this.subscriber.on('videoElementCreated', event => {
                        var videoElement = event.element;
                        var remoteVideoContainer = videoElement.parentNode;
                        var wrapper = document.createElement('div');
                        wrapper.className='col embed-responsive embed-responsive-4by3';
                        wrapper.id = videoElement.id + '__container'
                        remoteVideoContainer.removeChild(videoElement);
                        wrapper.appendChild(videoElement);
                        remoteVideoContainer.appendChild(wrapper);
                    });     
                    this.subscriber.on('videoElementDestroyed', event => {
                        document.getElementById(event.element.id + '__container').remove();
                    })               
                });

                getToken(mySessionId).then(token => {
                    session
                            .connect(token, data)
                            .then(() => {
                                resolve(session);
                            })
                            .catch(error => {
                                reject(error);
                            });
                });
            });
        },
        startStreaming() {
            return new Promise((resolve, reject)=>{
                try {
                    this.publisher = OV.initPublisher(undefined, {
                        audioSource: undefined, // The source of audio. If undefined default microphone
                        videoSource: undefined, // The source of video. If undefined default webcam
                        publishAudio: true,  	// Whether you want to start publishing with your audio unmuted or not
                        publishVideo: true,  	// Whether you want to start publishing with your video enabled or not
                        resolution: '640x480',  // The resolution of your video
                        frameRate: 60,			// The frame rate of your video
                        insertMode: 'APPEND',	// How the video is inserted in the target element 'video-container'
                        mirror: false       	// Whether to mirror your local video or not
                    });
                    this.publisher.addVideoElement(document.getElementById('localVideo'));
                    session.publish(this.publisher);
                    resolve(session);
                }
                catch(e) {
                    reject(e);
                }
            });            
        },
        stopStreaming() {
            try {
                session.unpublish(this.publisher);              
            }
            catch(e) {
                console.log(e);
            }
        },
        leaveSession() {
            session.disconnect();
        },
        sendSignal(signalType, data, callback) {
            var type = signalType || 'message';
            session.signal({
                type,
                data,
                to: []        
            })
                    .then(()=>{
                        if (callback && typeof(callback) == 'function' ) {
                            callback();
                        }  
                    });
        },
        listenForSignal(signalType, callback) {
            var type = signalType || 'message';
            session.on('signal:'+type, (event)=>{
                if (callback && typeof(callback) == 'function' ) {
                    callback(event);
                }  
            });
        }
    };
    
    window.onbeforeunload = function () {
        if (session)
            session.disconnect();
    };

    return manager;
}

/**
 * --------------------------
 * SERVER-SIDE RESPONSIBILITY
 * --------------------------
 * These methods retrieve the mandatory user token from OpenVidu Server.
 * This behavior MUST BE IN YOUR SERVER-SIDE IN PRODUCTION (by using
 * the API REST, openvidu-java-client or openvidu-node-client):
 *   1) Initialize a session in OpenVidu Server	(POST /api/sessions)
 *   2) Generate a token in OpenVidu Server		(POST /api/tokens)
 *   3) The token must be consumed in Sessi1on.connect() method
 */

var OPENVIDU_SERVER_URL = process.env.MIX_OPENVIDU_WEB_URL || "https://" + location.hostname;
var OPENVIDU_SERVER_SECRET = process.env.MIX_OPENVIDU_SECRET || 'MY_SECRET';

async function getToken(mySessionId) {
    const token = await createSessionAndToken(mySessionId);
    console.log('token', token);
    return typeof(token.token) == 'object' && token.token.token ? token.token.token : token.token;
}

async function createSessionAndToken(sessionId) {
    // See https://openvidu.io/docs/reference-docs/REST-API/#post-apitokens
    try {
        return fetch(OPENVIDU_SERVER_URL + "/openvidu/token", {
            method: "POST",
            headers: {
                Authorization: "Basic " + btoa("OPENVIDUAPP:" + OPENVIDU_SERVER_SECRET),
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                session: {
                    mediaMode: 'ROUTED',
                    recordingMode: 'ALWAYS',
                    customSessionId: sessionId,
                    defaultOutputMode: 'COMPOSED',
                    defaultRecordingLayout: 'BEST_FIT'
                },
                tokenOptions: {
                    role: 'PUBLISHER',
                    data: ''
                }
            })
        })
                .then(response => {
                    return response.json();
                })
                .catch(error => {
                    throw error;
                });
    } catch (error) {
        return error;
    }
}
