<template>
    <b-container>
        <b-row v-if="videos && videos.length">
            <video-preview :video="video" v-for="video in videos" :key="video.id" @play-video="playVideo"></video-preview>
            
        </b-row>
        <b-card v-else>
            {{ startMessage }}
        </b-card>
        <b-modal centered hide-footer id="videoplayer" title="Просмотр видео" v-model="showVideoPlayer">
            <div class="embed-responsive embed-responsive-4by3 mx-auto">
                <video controls class="">
                    Видео не поддерживается вашим браузером
                    <source :src="currentVideo ? currentVideo.url : ''" type="video/mp4" />
                </video>
            </div>
        </b-modal>
    </b-container>
</template>

<script>
import VideoPreview from '../../components/laravel-video-chat/VideoPreview'

export default {
    props:['videosProp', 'conversationId'],
    components: {
        VideoPreview
    },
    data(){
        return {
            showVideoPlayer: false,
            currentVideo: null,
            videos: !this.videosProp || !this.videosProp.length ? [] : !this.videosProp,
            startMessage: 'Здесь будут отображены видеозаписи,сделанные в ходе беседы'
        }
    },
    methods: {
        playVideo(video) {
            this.showVideoPlayer = !this.showVideoPlayer; 
            this.currentVideo = video
        }
    },
    mounted() {
        if (!this.videos || !this.videos.length) {
            axios.get(`/api/chat/${this.conversationId}/recordings`).then((response) => {
                if (!response.data || !response.data.length) {
                    this.startMessage = 'Видеозаписи отсутствуют'
                }
                else {
                    this.videos = response.data
                }
                
            })
        }
        
    }
}
</script>

<style lang="sass" scoped>

</style>