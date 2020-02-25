<template>
    <b-container>
        <form @submit.prevent="addParticipant">
            <div class="form-group" >
                <label>Название беседы <input type="text" name="conversation"></label>
            </div>
            <div class="input-group">
                <button type="submit">Войти в беседу</button>
            </div>
        </form>
    </b-container>
</template>

<script>
    export default {
        
        data() {
            return {
                
            }
        },
        methods: {
            addParticipant() {
                axios.post('/api/chat').then((response)=>{
                    this.$router.push('/api/chat/'+response.data.conversationId)
                }, ()=>{
                    alert('Произошла ошибка при добавлении участника в беседу')
                });
            }
        },
        mounted() {
            
        }
    }
</script>

<style scoped>
    
</style>