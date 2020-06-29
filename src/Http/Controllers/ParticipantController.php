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
     * @todo оптимизировать запрос
     * @param int $conversation 
     * @return Response
     */
    public function index() {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id'
        ]);        
        $data = $conversation->users()->with('profile')->get();
        return ['success'=>true, 'participants'=>$data];
    }

    /**
     * 
     * @param int $conversation 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request) {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'users' => 'required|array',
        ]);
        $users = $request->get('users', []);
        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав добавлять участников в беседу'
            ], 403);
        }
        if (!empty($users)) {
            $conversation->users()->attach($users);
            $conversation->save();
        }
        return response()->json([
            'success' => true,
        ], 201);
    }

    /**
     * 
     * @param int $conversation 
     * @param int $participant
     * @return Response
     */
    public function destroy($participant) {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id'
        ]);
        if (auth()->user()->id != $participant) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав удалять участников из беседы. Вы можете удалить только себя'
            ], 403);
        }
        $conversation->users()->detach($participant);
        return [
            'success' => true,
        ];
    }
}
