<template>
    <b-container class="laravel-video-chat">
        <router-view></router-view>
    </b-container>
</template>

<script>
    export default {
        props: ['user'],
        data() {
            return {
                
            }
        },
        mounted() {
            this.$store.commit('setUser', this.user)
        }
    }
</script>

<style scoped>
    
</style>