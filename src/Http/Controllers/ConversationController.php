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
use SquareetLabs\LaravelOpenVidu\Facades\OpenVidu;
use SquareetLabs\LaravelOpenVidu\Http\Requests\GenerateTokenRequest;
use SquareetLabs\LaravelOpenVidu\Builders\SessionPropertiesBuilder;
use SquareetLabs\LaravelOpenVidu\Builders\TokenOptionsBuilder;

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
    public function index(Request $request) {
        $paginator = Conversation::whereHas('users', function($query) {
            $query->where('users.id', auth()->user()->id);
        })->with('users.profile')->paginate($request->get('per_page', 10));

        return [
            'success' => true,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'pages' => $paginator->lastItem(),
            'items' => $paginator->items(),
            'total' => $paginator->total()
        ];
    }

    /**
     * Join existing conversation or start a new one
     * 
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'users' => 'array',
            'users.*' => 'integer|not_exists:users,id'
        ]);
        $conversation = Conversation::firstOrCreate(['name' => $request->get('name')]);
        $users = $request->get('users', []);
        if (!$conversation->users()->find(auth()->user()->id)) {
            $users[] = auth()->user()->id;   
        }             
        $conversation->users()->attach($users);
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
        return [
            'success'=>true,
            'conversation' => Chat::getConversationMessageById($conversation)
        ];
    }

    /**
     * Delete conversation
     * 
     * @param int $id Conversation id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Conversation $conversation) {
        if ($conversation->users->isEmpty()) {
            $id = $conversation->id;
            $conversation->delete();
            return [
                'conversation' => $id,
                'success' => true
            ];
        }        
        return response()->json([
            'message'=>'Нельзя удалить беседу, если там кто-то есть',
            'success' => false
        ], 403);
    }

    public function enter(Request $request) {
        $conversation = Conversation::firstOrCreate([
            'name' => $request->get('name')
        ]);
        $userId = auth()->user()->id;
        if (!$conversation->users()->find($userId)) {
            $conversation->users()->attach($userId);
        }
        
        /**
         * @todo Подумать над рутами для фронта
         */
        return [
            'status'=>'success',
            'to'=> '/chat/'.$conversation->id
        ];
    }

    /**
     * Get OpenVidu token
     * 
     * @todo Скорее всего, этой команде здесь не место
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function token(Request $request) {
        $session = OpenVidu::createSession(SessionPropertiesBuilder::build($request->get('session')), $request->get('force'));
        $token = $session->generateToken(TokenOptionsBuilder::build($request->get('tokenOptions')));
        return response()->json(['token' => $token], 200);
    }
}
