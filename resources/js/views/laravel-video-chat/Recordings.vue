<template>
    <b-container>
        <b-row>
            <video-preview :video="video" v-for="video in videos" :key="video.id" @play-video="playVideo"></video-preview>
            
        </b-row>
        <b-modal id="videoplayer" title="Входящий вызов" v-model="showVideoPlayer">
            <div class="embed-responsive embed-responsive-4by3 local-video-container">
                <video class="">
                    Видео не поддерживается вашим браузером
                    <source :src="currentVideo ? currentVideo.url : ''"/>
                </video>
            </div>
            <template slot="modal-footer">
                
            </template>
        </b-modal>
    </b-container>
</template>

<script>
export default {
    props: {
        videos: {
            type: Array,
            default() {
                return [];
            }
        }
    },
    data(){
        return {
            showVideoPlayer: false,
            currentVideo: null,
        }
    },
    methods: {
        playVideo(video) {
            this.showVideoPlayer = !!this.showVideoPlayer; 
            this.currentVideo = video
        }
    }
}
</script>

<style lang="sass" scoped>

</style>