export default [
    { 
        path: '/chat', 
        component: require('../views/laravel-video-chat/Index').default,
        children: [
            {
                path: '', 
                component: require('../views/laravel-video-chat/Conversations').default,
            },
            {
                path: ':conversationId',
                component: require('../views/laravel-video-chat/ChatRoom').default,
                props: true
            }
        ]
    }
];
