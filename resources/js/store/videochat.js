export default {
    namespaced: true,
    state: {
        conversations: [],
        currentConversation: null
    },
    mutations: {
        addConversation(state, conversation) {
            let conversations = state.conversations;
            let item = conversations.find((item)=>item.id == conversation.id);
            if (!item) { 
                conversations.push(conversation);                
            }
            else {
                conversations[conversations.indexOf(item)] = conversation;
            }
            state.conversations = conversations;
        },
        setCurrentConversation(state, conversationId) {
            let conversation = state.conversations.find((item)=>item.id == conversationId);
            state.currentConversation = conversation;
        }

    },
    actions: {
        async toConversation({commit, state}, conversationId) {    
            try {
                const response = await axios.get(`/api/chat/${conversationId}`);
                const conversation = response.data.conversation; 
                commit('addConversation', conversation);
                commit('setCurrentConversation', conversationId);
                return conversation;
            }
            catch(e) {
                alert('При загрузке беседы произошла ошибка');
                console.log(e);
            }            
        }
    }
}