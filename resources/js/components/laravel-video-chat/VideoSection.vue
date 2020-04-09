<template>
    <div :class="'videosection card '">
        <div class="card-body">
            <!--<div class="video-container" :class="{full : fullscreen}">-->
            <div class="video-container full">
                <div class="row" id="remoteVideo" >
                    
                </div>
                <div class="embed-responsive embed-responsive-1by1 local-video-container">
                    <video id="localVideo" muted="muted" class="">
                        Видео не поддерживается вашим браузером
                    </video>
                </div>
            </div>       
        </div>
        <div class="card-footer">
            <button class="btn btn-danger" @click="endCall">
                <!--<fai icon="phone-slash"></fai>-->
                Завершить
            </button> 
            <button class="btn btn-primary" @click="toggleFullScreen">
                <b-icon icon="tv"></b-icon>
                {{ fullscreenButton }}
            </button> 
            <button class="btn btn-primary" @click="toggleScreenShare">
                <b-icon icon="desctop"></b-icon>
                {{ screenShareButton }}
            </button> 
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                fullscreen: false,
                fullscreenButton: 'Полный экран',
                screenShareButton: "Показ экрана",
                screenShare: false
            }
        },
        methods: {
            /**
             * @fires Event#end-call
             */
            endCall(){
                this.toggleFullScreen(false);
                /**
                 * @event Event#end-call
                 * @type {object} 
                 */
                this.$emit('end-call');
            },
            /**
             * @param {boolean} flag 
             */
            toggleFullScreen(flag) {
                //Есть ли полный экран сейчас 
                var state = !!document.fullscreenElement;
                //Был ли задан флаг
                var hasFlag = !(flag === undefined || flag === null);;
                /*
                    Если флага не было, то полный экран нужен при его отсутствии и не нужен при наличии 
                    Если флаг был, то полный экран нужен, когда флаг положительный и полного экрана нет
                */
                /**
                 * @todo Исправить логику так, чтобы в консоль не шла ошибка "not in fullscreen mode"
                 */
                var doFullScreen = !hasFlag ? !state : (flag && !state);
                if (doFullScreen) {
                    document.querySelector('.chat-room .videosection').requestFullscreen();
                }
                else if (!state && hasFlag && !flag) {
                    return;
                }
                else {
                    document.exitFullscreen();                
                }
            },
            /**
             * @fires Event#toggle-screen-share
             */
            toggleScreenShare() {
                this.screenShare = !this.screenShare;
                this.screenShareButton = this.screenShare ? 'Остановить показ' : 'Демонстрация экрана';
                /**
                 * @event Event#toggle-screen-share
                 * @type {object} 
                 * @property {boolean} flag 
                 */
                this.$emit('toggle-screen-share', this.screenShare);
            },
        },
        mounted() {
            document.querySelector('.chat-room .videosection').onfullscreenchange = ()=> {
                console.log('Fullscreen:', this.fullscreen)
                this.fullscreen = !this.fullscreen;
                this.fullscreenButton = this.fullscreen ? 'Обычный режим' : 'Полный экран';   
            }
        }
    }
</script>

<style lang="scss">

    .video-container{
        position: relative;
    }

    .video-container.full{
        &, #remoteVideo {
            height: 100%;
        }
    }

    #remoteVideo .embed-responsive.embed-responsive-4by3 {
        max-height: 100%;
    }

    .local-video-container {
        width: 20%;
        height: 20%;
        position: absolute;
        bottom: 20px;
        right: 5px;
    }
</style>