export default [
    { 
        path: '/chat', 
        component: require('../views/laravel-video-chat/Index').default,
        meta: { validate: ['auth','two_factor','lock_screen'] },
        children: [
            {
                path: '', 
                component: require('../views/laravel-video-chat/Conversations').default,
            },
            {
                path: ':conversationId',
                component: require('../views/laravel-video-chat/ChatRoom').default,
                props: true
            },
            {
                path: ':conversationId/recordings',
                component: require('../views/laravel-video-chat/Recordings').default,
                props: true
            }
        ]
    }
];
