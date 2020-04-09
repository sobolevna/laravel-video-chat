<template>
    <b-container fluid>
        <b-card title="Для входа в беседу введите её название">
            <form @submit.prevent="addParticipant">
                <div class="form-group" >
                    <label>Название беседы: <input type="text" name="conversation" v-model="conversation" required></label>
                    <b-button variant="success" type="submit">Войти в беседу</b-button>
                </div>
            </form>
            <p>Пользователь войдёт в беседу с таким названием или автоматически создастся новая</p>
        </b-card>
        <b-card title="Список бесед пользователя" v-if="conversationList.length">
            <table class="table table-striped table-hover">
                <thead >
                    <tr class="d-none d-md-table-row">
                        <th>Идентификатор </th>
                        <th>Название </th>
                        <th>Создана</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in conversationList" :key="item.id">
                        <th>{{ item.id }}</th>
                        <td>{{ item.name }}</td>
                        <td>{{ (new Date(item.created_at)).toLocaleString('ru') }}</td>
                        <td>
                            <div class="button-group">
                                <button-responsive variant="primary" @click="toConversation(item.id)" icon="chat" title="Перейти в беседу" size='sm'></button-responsive>
                                <button-responsive variant="info" :to="`chat/${item.id}/recordings`" icon="play" title="Просмотреть записи" size='sm'></button-responsive>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </b-card>
    </b-container>
</template>

<script>
    import ButtonResponsive from '../../components/laravel-video-chat/ButtonResponsive';
    export default {
        components: {
            ButtonResponsive
        },
        data() {
            return {
                conversation: '',
                conversationList: []
            }
        },
        methods: {            
            addParticipant() {
                axios.post('/api/chat', {
                    conversation: this.conversation
                }).then((response)=>{
                    this.$router.push('/chat/'+response.data.conversationId)
                }, ()=>{
                    alert('Произошла ошибка при добавлении участника в беседу')
                });
            },
            /**
             * @param {number} id
             */
            toConversation(id) {
                this.$store.dispatch('videochat/toConversation', id).then(()=>{
                    console.log(this.$store.state.videochat.currentConversation);
                    this.$router.push('/chat/'+id);
                })
            }
        },
        mounted() {
            axios.get('/api/chat/').then(response=>{
                this.conversationList = response.data;
            });
        }
    }
</script>

<style scoped>
    
</style>