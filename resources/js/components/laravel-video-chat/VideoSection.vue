<template>
    
        
            <div class="video-container">
                <div class="row" id="remoteVideo" >
                    
                </div>
                <div class="embed-responsive embed-responsive-1by1 local-video-container">
                    <video id="localVideo" muted="muted" class="">
                        Видео не поддерживается вашим браузером
                    </video>
                </div>
            </div>
        
        
</template>

<script>
    export default {
        props: ['visible'],
        data() {
            return {
                
            }
        },
        methods: {
            toggleFullScreen() {
                let element = $('.videosection.visible')[0];
                if (!document.fullscreenElement) {
                    element.requestFullscreen();
                    $('.video-container').toggleClass('video-fart-rem', true);
                    $('.embed-responsive.embed-responsive-4by3').toggleClass('video-full', true);
                    $('#remoteVideo').toggleClass('video-full', true);
                    $('.embed-responsive embed-responsive-1by1 local-video-container').toggleClass('local-video-container', false);
                    $('.embed-responsive embed-responsive-1by1 local-video-container').toggleClass('video-full-fart', true);
                }
                else {
                    document.exitFullscreen();

                }
            }, 
            finish() {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                    $('.video-container').toggleClass('video-fart-rem', false);
                    $('.embed-responsive.embed-responsive-4by3').toggleClass('video-full', false);
                    $('#remoteVideo').toggleClass('video-full', false);
                    $('.embed-responsive embed-responsive-1by1 local-video-container').toggleClass('local-video-container', true);
                    $('.embed-responsive embed-responsive-1by1 local-video-container').toggleClass('video-full-fart', false);
                }
                this.$emit('endCall');
            }
        }
    }
</script>

<style>
    .video-container {
        position: relative;
    }
    .local-video-container {
        width: 20%;
        height: 20%;
    }
    .embed-responsive.embed-responsive-1by1.local-video-container {
        position: absolute;
        bottom: 20px;
        right: 5px;
    }
    .card-body {
        /*overflow-x: hidden;
        max-height: max-content;
        height: 90%;*/
    }
    .video-fart{
        height: 40%;
    }
    .video-fart-rem{
        height: 100%;
    }
    .video-full-fart{
        top: 200px;
        height: 100%;
    }
    .video-full{
        height: 100%;
    }
</style>