<template>
    <div>
        <div class="row chat-room">            
            <div class="col-md-6" v-show="showVideo">
                <div :class="'videosection card '">
                    <div class="card-body h-auto">
                        <video-section :visible="showVideo" @endCall="endCall()"></video-section>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-danger" @click="endCall()">Завершить</button> 
                        <!--<button class="btn btn-primary" @click="">Полный экран</button> -->
                    </div>
                </div>
            </div>
            <div :class="showVideo ? 'col-md-6' : 'col-md-12'">
                <b-card no-body bg-variant="lite">
                    <b-card-header>
                        <b-icon icon="chat"></b-icon> Сообщения 

                        <button class="btn btn-primary btn-sm float-right" @click="startVideoCall()" type="button">
                            <b-icon icon="camera-video-fill"></b-icon> Видеозвонок
                        </button>
                        <button class="btn btn-info btn-sm float-right" @click="showRecordings()" type="button">
                            <b-icon-play></b-icon-play>Видеозаписи
                        </button>
                    </b-card-header>
                    <ul class="chat card-body" v-chat-scroll>
                        <li class="clearfix" v-for="message in messages" :key="message.id" v-bind:class="{ 'right' : check(message.sender.id), 'left' : !check(message.sender.id) }">
                            <span class="chat-img" v-bind:class="setMessageClasses(message.sender.id)">
                                <img :src="getSenderAvatar(message)" alt="User Avatar" class="img-circle" />
                            </span>
                            <div class="chat-body clearfix" v-bind:class="setMessageClasses(message.sender.id)">
                                <div class="header">
                                    <small class=" text-muted"><span class="fa fa-clock-o"></span><timeago :datetime="message.created_at" :auto-update="10"></timeago></small>
                                    <strong  class="primary-font">
                                        {{ message.sender.name }}
                                    </strong>
                                </div>
                                <p v-bind:class="setMessageClasses(message.sender.id)">
                                    {{ message.text }}
                                </p>
                                <div class="clearfix"></div>
                                <div class="row">
                                    <file-preview :file="file" v-for="file in message.files" :key="file.id"></file-preview>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <b-card-footer>
                        <div class="input-group">
                            <b-form-textarea
                                id="textarea"
                                v-model="text"
                                placeholder="Введите текст..."
                                rows="1"
                                max-rows="6"
                                @keyup.ctrl.enter="send()"
                            ></b-form-textarea>
                            <span class="input-group-btn">
                                <button class="btn btn-primary btn-sm" type="button" @click.prevent="send()" id="btn-chat" v-if="!loadingChat">
                                    <b-icon icon="cursor"></b-icon> 
                                    Отправить
                                </button>
                                <button class="btn btn-primary btn-sm" type="button" disabled v-else>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Отправляется...
                                </button>
                            </span>
                        </div>
                        <div class="form-group">
                            <b-form-file 
                                multiple 
                                browse-text="Обзор"
                                v-model="filesForUpload"
                                :state="Boolean(filesForUpload.length)"
                                placeholder="Выберите файлы..."
                                drop-placeholder="Опустите файлы..."
                            ></b-form-file>
                        </div>
                    </b-card-footer>
                </b-card>
            </div>
            
            <b-modal id="incomingVideoCallModal" title="Входящий вызов" v-model="incomingVideoCallModalShow">
                <template slot="modal-footer">
                    <button type="button" id="answerCallButton" class="btn btn-success" @click="answer()">Ответить</button>
                    <button type="button" id="denyCallButton" data-dismiss="modal" class="btn btn-danger">Отклонить</button>
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

    export default {
        components: {
            VideoSection,
            FilePreview
        },
        props: ['conversationId'],
        data() {
            return {
                filesForUpload: [],
                conversation: null,
                channel: '',
                messages: [],
                withUsers: [],
                text: '',
                showVideo: false,
                openViduManager: new OpenViduManager,
                loadingChat: false,
                incomingVideoCallModalShow: false,
                currentUser: this.$store.state.auth.user,
                conversationFiles: []
            }
        },
        methods: {
            startVideoCall() {
                let self = this;
                this.openViduManager.startStreaming().then(()=>{
                    self.showVideo = true;
                    var message = {from: this.currentUser.id, type: 'signal', subtype: 'offer', content: '', time: new Date()};
                    self.openViduManager.sendSignal('offer', JSON.stringify(message));
                }, (e)=>console.log(e));
            },
            check(id) {
                return id === this.currentUser.id;
            },
            send() {
                this.toggleLoadingChat(true);
                var text = this.text;
                var message = {
                    conversationId: this.conversationId,
                    sender: this.currentUser,
                    text,
                    created_at: new Date()
                };
                if (this.filesForUpload && this.filesForUpload.length) {
                    this.sendFiles().then((response)=>{
                        message.files = response.data.files;
                        message.text = !message.text ? response.data.text: message.text;
                        this.sendMessage(message);
                        this.filesForUpload = []
                    }, (response)=>{
                        alert('Произошла ошибка при отправке файлов');
                        this.toggleLoadingChat(false);
                    });
                }
                else {
                    this.sendMessage(message);
                }                
            },
            sendMessage(message) {
                this.openViduManager.sendSignal('message', JSON.stringify(message), ()=>{
                    this.text = '';
                    axios.post('/api/chat/message/send', message);
                    this.toggleLoadingChat(false);
                });
            },
            async sendFiles() {
                var data = new FormData();

                $.each(this.filesForUpload, function (key, value)
                {
                    data.append('files[]', value);
                });

                data.append('conversationId', this.conversationId);

                return axios.post('/api/chat/message/send/file', data);
            },        
            toggleLoadingChat(flag) {
                this.loadingChat = !!flag;
            },
            listenForNewMessage: function () {
                this.openViduManager.listenForSignal('message',(event)=>{
                    var data = JSON.parse(event.data);
                    if (data.files && data.files.length > 0) {
                        data.files.forEach((item) =>{
                            this.conversation.files.push(item);
                        });
                    }
                    this.messages.push(data);
                });
                this.openViduManager.listenForSignal('offer',()=>{
                    this.handleCall();
                });
                this.openViduManager.listenForSignal('close',()=>{
                    this.endCall(true);
                });
            },
            hideVideo() {
                this.showVideo = false;
            },
            answer() {
                var self = this;
                this.incomingVideoCallModalShow = false;
                self.showVideo = true;  
                this.openViduManager.startStreaming().then(()=>{
                    var message = {from: self.currentUser.id, type: 'signal', subtype: 'answer', content: '', time: new Date()};
                    //return axios.post('/trigger/' + self.conversationId, message);
                });
            },
            endCall(fromSignal) {
                this.showVideo = false;
                this.openViduManager.stopStreaming();
                if (fromSignal) {
                    return;
                }
                var message = {from: this.currentUser.id, type: 'signal', subtype: 'close', content: '', time: new Date()};
                this.openViduManager.sendSignal('close', JSON.stringify(message),()=>{
                    //axios.post('/trigger/'+this.conversationId, message);
                });                
            },
            handleCall() {
                if (!this.showVideo) {
                    this.incomingVideoCallModalShow = true;
                }
            },
            setMessageClasses(senderId) {
                var obj = { 
                    'float-right': this.check(senderId), 
                    'text-right': this.check(senderId), 
                    'float-left' : !this.check(senderId),
                    'text-left' : !this.check(senderId),
                };
                return obj;
            },
            prepareUpload(event) {
                this.filesForUpload = event.target.files;
            },
            getSenderAvatar(message) {
                return message.sender.avatar || 'https://placehold.it/50/FA6F57/fff&text='+ message.sender.name;
            },
            async getConversationDetails(conversationId) {
                axios.get(`/api/chat/${conversationId}`).then((response) =>{
                    this.conversation = response.data.conversation; 
                    this.conversationFiles = response.data.conversation.files
                    this.messages = response.data.conversation.messages;
                    this.withUsers = response.data.conversation.users;
                    this.channel = response.data.conversation.channel;
                }, (response)=>{
                    alert('При загрузке беседы произошла ошибка');
                    console.log(response);
                });
            },
            showRecordings() {
                this.$router.push({
                    path: this.conversationId+'/recordings'
                });                
            }
        },
        mounted() {
            this.getConversationDetails(this.conversationId).then(()=>{
                this.openViduManager.joinSession(this.conversationId)
                    .then(()=>this.listenForNewMessage());
            });
            
        }
    }    

</script>

<style scoped lang="scss">
    .chat {
        list-style: none;
        margin: 0;
        padding: 0;
        
        li {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #B3A9A9;
            
            .chat-body p {
                margin: 0;
                color: #777777;
            }
        }
    }

    .chat-room {
        .card .slidedown .glyphicon, .chat .glyphicon
        {
            margin-right: 5px;
        }

        .card-body
        {
            overflow-y: scroll;
            height: 250px;
        }
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
