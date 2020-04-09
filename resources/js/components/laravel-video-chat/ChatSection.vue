<template>
    <b-card no-body bg-variant="lite">
        <b-card-header>
            <b-icon icon="chat"></b-icon> Сообщения 
        </b-card-header>
        <ul class="chat card-body" v-chat-scroll>
            <li class="clearfix chat-item mx-2" v-for="message in conversation.messages" :key="message.id" v-bind:class="{ 'right' : check(message.sender.id), 'left' : !check(message.sender.id) }">
                <span class="chat-img" v-bind:class="setMessageClasses(message.sender.id)">
                    <img :src="getSenderAvatar(message)" alt="User Avatar" class="img-circle" />
                </span>
                <div class="chat-body clearfix" v-bind:class="setMessageClasses(message.sender.id)">
                    <div class="message-header">
                        <small class=" text-muted"><span class="fa fa-clock-o"></span><timeago :datetime="message.created_at" :auto-update="10"></timeago></small>
                        <strong  class="primary-font">
                            {{ getSenderName(message.sender) }}
                        </strong>
                    </div>
                    <p class="message-body" v-bind:class="setMessageClasses(message.sender.id)" v-html="lineBreaks(message.text)">
                        
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
                    <button-responsive
                        variant="info"
                        size="sm"
                        @click="send()"
                        v-b-tooltip.hover 
                        title="Отправить по Ctrl+Enter"
                        id="btn-chat" v-if="!loadingChat"
                        icon="cursor"
                    >Отправить</button-responsive>
                    <button class="btn btn-primary btn-sm" type="button" disabled v-else>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span class="d-none d-md-inline">Отправляется...</span>
                    </button>
                </span>
            </div>
            <div class="form-group">
                <b-form-file 
                    multiple 
                    browse-text="Обзор"
                    v-model="filesForUpload"
                    placeholder="Выберите файлы..."
                    drop-placeholder="Опустите файлы..."
                ></b-form-file>
            </div>
        </b-card-footer>
    </b-card>
</template>

<script>
import ButtonResponsive from "../../components/laravel-video-chat/ButtonResponsive";
import FilePreview from "../../components/laravel-video-chat/FilePreview";

export default {
    components: {
        ButtonResponsive,
        FilePreview
    },
    props: ['currentUser', 'loadingChat', 'sendMessage'],
    data() {
        return {
            text: '',
            filesForUpload: [],
        }
    },
    methods: {
        /**
         * @param {number} id
         * @returns {boolean}
         */
        check(id) {
            return id === this.currentUser.id;
        },
        send() {
            this.toggleLoadingChat(true);
            var text = this.text;
            var message = {
                conversationId: this.conversation.id,
                sender: this.currentUser,
                text,
                created_at: new Date()
            };
            message.sender.profile = {
                avatar: this.currentUser.avatar,
                first_name:this.currentUser.first_name,
                middle_name:this.currentUser.middle_name,
                last_name:this.currentUser.last_name
            }
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
            this.text= '';
        },
        /**
         * @async
         * @returns {Promise<Response>}
         */
        sendFiles() {
            var data = new FormData();

            $.each(this.filesForUpload, function (key, value)
            {
                data.append('files[]', value);
            });

            data.append('conversationId', this.conversation.id);

            return axios.post('/api/chat/message/send/file', data);
        },  
        /**
         * @param {boolean} flag 
         * @fires Event#loading-chat
         */
        toggleLoadingChat(flag) {
            /**
             * @event Event#loading-chat
             * @type {object} 
             * @property {boolean} flag 
             */
            this.$emit('loading-chat', !!flag);
        },
        /**
         * @param {number} senderId 
         * @returns {object}
         */
        setMessageClasses(senderId) {
            var obj = { 
                'float-right': this.check(senderId), 
                'text-right': this.check(senderId), 
                'float-left' : !this.check(senderId),
                'text-left' : !this.check(senderId),
            };
            return obj;
        },
        /**
         * @param {Event} event
         */
        prepareUpload(event) {
            this.filesForUpload = event.target.files;
        },
        /**
         * @param {object} message 
         * @returns {string}
         */
        getSenderAvatar(message) {
            return message.sender.avatar || 'https://placehold.it/50/FA6F57/fff&text='+ message.sender.name;
        },
        /**
         * @param {object} sender
         * @returns {string}
         */
        getSenderName(sender) {
            return `${sender.profile.first_name} ${sender.profile.middle_name || ''} ${sender.profile.last_name}`
        },
        lineBreaks(text) {
            if (!text) {
                return '';
            }
            return text.toString().replace(/(?:\r\n|\r|\n)/g, '<br />');
        }
    },
    computed: {
        conversation() {
            return this.$store.state.videochat.currentConversation;
        }
    }, 
    filters: {
        
    }
}
</script>

<style scoped lang="scss">
    .chat {
        list-style: none;
        margin: 0;
        padding: 0;
        overflow-y: scroll;

        li {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #B3A9A9;
            
            .message-body {
                /*margin: 3px;*/
                color: #777777;
            }
        }
    }

</style>