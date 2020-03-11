<template>
    <div class="col-md-3">
        <b-card class="d-flex justify-content-center">
            <div class="video-preview-image" @click="playVideo">
                <img :src="video.img_preview" alt="" class="img-thumbnail">
                <!--<div class="rounded-circle h1 bg-light-info mx-auto w-25"><b-icon-play font-scale="3"></b-icon-play></div>-->
                <h4 class="">Видео {{ video.id }}</h4>
                <p class="">Время начала записи: {{ createdAt }}</p>
            </div>
            
        </b-card>
        
    </div>
</template>

<script>
    export default {
        props: ['video'],
        methods: {
            playVideo() {
                this.$emit('play-video', this.video);
            }
        }, 
        computed: {
            createdAt() {
                return (new Date(this.video.createdAt)).toLocaleString('ru');
            }
        }
    }
</script>
