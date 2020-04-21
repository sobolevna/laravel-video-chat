<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\{Request, Response};
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Events\{
    VideoChatStart, VideoChatFinish
};
use Illuminate\Routing\Controller;
use Storage;
use Sobolevna\LaravelVideoChat\Models\Conversation;

/**
 * @todo Логику перенести в сервисные классы
 * @todo Авторизацию действий отправить в политики 
 * @todo Отправлять коды статусов
 */
class ParticipantController extends Controller
{
    /**
     * @param int $conversation 
     * @return Response
     */
    public function index($conversation) {
        return Conversation::with('users', 'users.profile')->findOrFail($conversation)->users;
    }

    /**
     * 
     * @param int $conversation 
     * @param Request $request
     * @return Response
     */
    public function store($conversation, Request $request) {
        $users = $request->get('users', []);
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав добавлять участников в беседу'
            ];
        }
        if (!empty($users)) {
            $conversationModel->attach($users);
            $conversationModel->save();
        }
        return [
            'success' => true,
        ];
    }

    /**
     * 
     * @param int $conversation 
     * @param int $participant
     * @return Response
     */
    public function destroy($conversation, $participant) {
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав добавлять участников в беседу'
            ];
        }
        $conversationModel->detach($participant);
        return [
            'success' => true,
        ];
    }
}
