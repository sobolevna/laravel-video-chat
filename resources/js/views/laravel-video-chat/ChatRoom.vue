<template>
    <div>
        <b-navbar>      
            <b-navbar-nav>
                <b-nav-item>
                    {{ conversation ? ' Беседа: '+conversation.name : '' }}
                </b-nav-item>
            </b-navbar-nav>
            <b-navbar-nav class="ml-auto">      
                <b-nav-item right>
                    <b-button-toolbar
                        class="float-right" 
                    >
                        <button-responsive                 
                            variant="info"
                            size="sm"
                            :to="`/chat/${conversationId}/recordings`"
                            v-b-tooltip.hover 
                            title="Видеозаписи"
                            icon="play"
                        >                            
                        </button-responsive>
                        <b-button-group>
                            <button-responsive 
                            variant="primary"
                            size="sm"
                            @click="startVideoCall()" 
                            v-b-tooltip.hover 
                            title="Видеозвонок"
                            icon="camera-video-fill"
                        >
                        </button-responsive>
                        </b-button-group>
                    </b-button-toolbar>
                </b-nav-item>     
            </b-navbar-nav>
        </b-navbar>        
        <div class="row chat-room vh-75">            
            <div class="chat-component col-md-6 h-100" v-show="showVideo">
                <video-section 
                    class="h-100"
                    @end-call="endCall" 
                    @toggle-screen-share="toggleScreenShare"
                ></video-section>
            </div>
            <div :class="'chat-component h-100 '+ (showVideo ? 'col-md-6' : 'col-md-12')">
                <chat-section 
                    class="h-100"
                    :loading-chat="loadingChat" 
                    :current-user="currentUser" 
                    :send-message="sendMessage"
                ></chat-section>
            </div>
            
            <b-modal 
                id="incomingVideoCallModal" 
                title="Входящий вызов" 
                v-model="incomingVideoCallModalShow" 
                @close="declineCall"
                @cancel="declineCall"
                @shown="audioManager.callAudio('ringtone', true)"
                @hidden="audioManager.callAudio('ringtone', false)"
                no-close-on-backdrop
            >
                <template slot="modal-footer">
                    <button type="button" id="answerCallButton" class="btn btn-success" @click="answer()">Ответить</button>
                    <button type="button" id="denyCallButton" data-dismiss="modal" @click="declineCall()" class="btn btn-danger">Отклонить</button>
                </template>
            </b-modal>
            
            <div class="row">
                <file-preview :file="file" v-for="file in conversationFiles" :key="file.id"></file-preview>
            </div>  
        </div>        
    </div>
</template>

<script>
    import { OpenViduManager } from "../../openvidu-app";
    
    import VideoSection from "../../components/laravel-video-chat/VideoSection";
    import FilePreview from "../../components/laravel-video-chat/FilePreview";
    import ButtonResponsive from "../../components/laravel-video-chat/ButtonResponsive";
    import ChatSection from "../../components/laravel-video-chat/ChatSection";
    import AudioManager from "../../services/audio";
    import NotificationManager from "../../services/notification";

    export default {
        components: {
            VideoSection,
            FilePreview,
            ButtonResponsive,
            ChatSection
        },
        props: ['conversationId'],
        data() {
            return {
                filesForUpload: [],
                conversation: this.$store.state.videochat.currentConversation,
                channel: '',
                withUsers: [],                
                showVideo: false,
                openViduManager: new OpenViduManager,
                audioManager: new AudioManager([
                    { 
                        name: 'message',
                        file: '/sounds/234524__foolboymedia__notification-up-1.wav',
                    },
                    { 
                        name: 'ringtone',
                        file: '/sounds/271152__chriswr__legacy-ringtone.mp3',
                        hasLoop: true
                    },
                    { 
                        name: 'calling',
                        file: '/sounds/271980__l3v1stus__phone-call.wav',
                        hasLoop: true
                    },
                ]),
                notificationManager: new NotificationManager(),
                incomingVideoCallModalShow: false,
                currentUser: this.$store.state.auth.user,
                conversationFiles: [],
                loadingChat: false,
                recordingId: ''
            }
        },
        methods: {
            async startVideoCall() {
                var loader = this.$loading.show();
                this.audioManager.callAudio('ringtone', false);
                try {
                    if (!this.recordingId) {
                        let response = await axios.post(`/openvidu/recordings/start`, {
                            session: this.conversationId,
                            name: ''
                        });
                        this.recordingId = response.data.recording.id;  
                    }                
                    await this.openViduManager.startStreaming();
                    this.showVideo = true;
                    var message = {from: this.currentUser.id, recordingId: this.recordingId, type: 'signal', subtype: 'offer', content: '', time: new Date()};
                    this.openViduManager.sendSignal('offer', JSON.stringify(message));
                }
                catch(e) {
                    alert("При подключении произошла ошибка");
                    console.log(e);
                }
                loader.hide();
            },            
            /**
             * @param {object} message
             */
            sendMessage(message) {
                this.openViduManager.sendSignal('message', JSON.stringify(message), ()=>{
                    this.text = '';
                    axios.post('/api/chat/message/send', message);
                    this.loadingChat = false;
                });
            },
            listenForNewMessage() {
                this.openViduManager.listenForSignal('message',(event)=>{
                    var message = event.data;
                    if (message.files && message.files.length > 0) {
                        message.files.forEach((item) =>{
                            this.conversation.files.push(item);
                        });
                    }
                    this.conversation.messages.push(message);
                    if (event.data.sender.id != this.currentUser.id) {
                        this.audioManager.callAudio('message');
                        let sender = `${message.sender.profile.first_name} ${message.sender.profile.middle_name || ''} ${message.sender.profile.last_name}`;
                        this.notificationManager.send(sender, {
                            body: message.text,
                            img: message.sender.avatar
                        });
                    } 
                });
                this.openViduManager.listenForSignal('offer',(event)=>{
                    let sameUser = event.data.from == this.currentUser.id;
                    this.recordingId = event.data.recordingId;
                    this.handleCall(sameUser);
                });
                this.openViduManager.listenForSignal('close',()=>{
                    if (event.data.from == this.currentUser.id) {
                        return;
                    }
                    this.endCall(true);
                });
            },
            hideVideo() {
                this.showVideo = false;
            },
            answer() {
                this.audioManager.callAudio('ringtone', false);
                this.incomingVideoCallModalShow = false;
                if (this.showVideo) {
                    return;
                }
                this.showVideo = true;  
                this.openViduManager.startStreaming().then(()=>{
                    var message = {from: this.currentUser.id, type: 'signal', subtype: 'answer', content: '', time: new Date()};
                    //return axios.post('/trigger/' + this.conversationId, message);
                });
            },
            /**
             * @param {boolean} fromSignal
             */
            endCall(fromSignal) {
                this.showVideo = false;
                this.openViduManager.stopStreaming();
                if (!fromSignal) {
                    var message = {from: this.currentUser.id, type: 'signal', subtype: 'close', content: '', time: new Date()};
                    this.openViduManager.sendSignal('close', JSON.stringify(message),()=>{
                        axios.post(`/openvidu/recordings/${this.recordingId}/stop`);
                    });  
                }
                /*this.openViduManager.leaveSession();
                this.openViduManager.joinSession(this.conversationId);*/
            },
            handleCall(sameUser) {
                this.notificationManager.send("Беседа: "+ this.conversation.name, {
                    body: "Видеозвонок"
                });
                if (!this.showVideo && !sameUser) {
                    this.incomingVideoCallModalShow = true;
                }
                else if(!this.showVideo && sameUser) {
                    this.showVideo = true;
                }
            },  
            declineCall() {
                this.audioManager.callAudio('ringtone', false);
                this.incomingVideoCallModalShow = false;
                if (this.showVideo) {
                    return;
                }
                var message = {from: this.currentUser.id, type: 'signal', subtype: 'decline', content: '', time: new Date()};
                this.openViduManager.sendSignal('close', JSON.stringify(message),()=>{
                    //axios.post(`/openvidu/recordings/${this.recordingId}/stop`);
                });  
            },
            /**
             * @param {number} conversationId
             */
            async getConversationDetails(conversationId) {
                axios.get(`/api/chat/${conversationId}`).then((response) =>{
                    this.conversation = response.data.conversation; 
                    this.conversationFiles = response.data.conversation.files
                    this.withUsers = response.data.conversation.users;
                    this.channel = response.data.conversation.channel;
                }, (response)=>{
                    alert('При загрузке беседы произошла ошибка');
                    console.log(response);
                });
            },
            showRecordings() {
                this.$router.push({
                    path: 'recordings'
                });                
            },
            /**
             * @param {boolean} doScreenShare
             */
            toggleScreenShare(doScreenShare) {
                if (doScreenShare) {
                    this.openViduManager.screenShareStart();
                }
                else {
                    this.openViduManager.screenShareStop();
                }
            },

        },
        computed: {

        },
        created(){
            this.openViduManager.joinSession(this.conversationId)
                    .then(()=>this.listenForNewMessage());
            this.$store.dispatch('videochat/toConversation', this.conversationId).then((conversation)=>{
                this.conversation = conversation;                 
            });
            this.notificationManager.requestPermissions();
        },
        mounted() {
            
        },
        beforeDestroy() {
            console.log('beforeDestroy');
            this.openViduManager.leaveSession();
        }
    }    

</script>

<style scoped lang="scss">    

    .chat-room {

        height: 75vh;

        .card .slidedown .glyphicon, .chat .glyphicon
        {
            margin-right: 5px;
        }

        /*.chat-component
        {
            min-height: 250px;
            max-height: 80vw;
        }*/
    }
    
    ::-webkit-scrollbar-track
    {
        box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        background-color: #F5F5F5;
    }

    ::-webkit-scrollbar
    {
        width: 12px;
        background-color: #F5F5F5;
    }

    ::-webkit-scrollbar-thumb
    {
        box-shadow: inset 0 0 6px rgba(0,0,0,.3);
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
        background-color: #555;
    }
</style>
