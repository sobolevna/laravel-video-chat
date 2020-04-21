<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Events\{
    VideoChatStart, VideoChatFinish
};
use Sobolevna\LaravelVideoChat\Models\Conversation;
use Illuminate\Routing\Controller;


/**
 * @todo Логику перенести в сервисные классы
 * @todo Авторизацию действий отправить в политики 
 * @todo Отправлять коды статусов
 */
class ConversationController extends Controller
{
    /**
     * Список всех бесед, доступных пользователю
     */
    public function index() {
        return response()->json(['success'=>true, 'conversations'=> Chat::getAllConversations()], 200);
    }

    /**
     * Join existing conversation or start a new one
     * 
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conversation = Chat::addParticipant($request->get('name'), auth()->user()->id);
        return response()->json(['success'=>true, 'conversationId'=>$conversation->id], 201);
    }

    /**
     * Get conversation data
     * 
     * @param int $id Conversation id
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function show($conversation, Request $request)
    {
        try {
            $conversationModel = Chat::getConversationMessageById($conversation);
        }
        catch (\ErrorException $e) {
            return $e->getMessage();
        }

        return [
            'conversation' => $conversationModel
        ];
    }

    /**
     * Delete conversation
     * 
     * @param int $id Conversation id
     * @return \Illuminate\Http\Response
     */
    public function destroy($conversation) {
        $conversationModel = Conversation::with('users')->findOrFail($conversation);
        if ($conversationModel->users->isEmpty()) {
            $conversationModel->delete();
            return [
                'conversation' => $conversation,
                'success' => true
            ];
        }        
        return response()->json([
            'message'=>'Нельзя удалить беседу, если там кто-то есть',
            'success' => false
        ], 403);
    }
}
