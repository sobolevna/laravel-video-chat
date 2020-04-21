<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\{Request,Response};
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use Storage;
use Sobolevna\LaravelVideoChat\Models\{Conversation, Message};

/**
 * @todo Логику перенести в сервисные классы
 * @todo Авторизацию действий отправить в политики 
 * @todo Отправлять коды статусов
 * @todo Помирить с тем, что сообщения будут отправляться через OpenVidu
 */
class MessageController extends Controller
{
    /**
     * @param int $conversation 
     * @return Response
     */
    public function index($conversation) {
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав добавлять участников в беседу'
            ];
        }
        return [
            'success' => true,
            'messages' => $conversationModel->messages
        ];
    }

    /**
     * Send a message
     * 
     * @param int $conversation
     * @param Request
     * @return Response
     */
    public function store($conversation, Request $request)
    {
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ], 403);
        }
        $message = $conversationModel->messages()->create([
            'user_id'=>auth()->user()->id,
            'text'=>$request->get('text'),
        ]);

        $files = $request->get('files', null);
        if ($files) {
            $message->files()->attach($files);
        }
        
        return response()->json([
            'success' => true,
            'message' => "Сообщение отправлено"
        ], 201);
    }

    /**
     * Update message
     * 
     * @param int $conversation
     * @param int $message
     * @param Request $request
     * @return Response
     */
    public function update($conversation, $message, Request $request) {
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ];
        }
        $messageModel = Message::findOrFail($message);
        if ($messageModel->user_id != auth()->user()->id) {
            return [
                'success' => false,
                'message' => 'Вы не являетесь автором сообщения'
            ];
        }
        $messageModel->text = $request->get('text');
        $messageModel->attach($request->get('files'));
        $messageModel->save();
        return [
            'success' => true,
            'message' => "Сообщение отправлено"
        ];
    }

    /**
     * Delete message
     * 
     * @param int $conversation
     * @param int $message
     * @param Request $request
     * @return Response
     */
    public function destroy($conversation, $message) {
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ];
        }
        $messageModel = Message::findOrFail($message);
        if ($messageModel->user_id != auth()->user()->id) {
            return [
                'success' => false,
                'message' => 'Вы не являетесь автором сообщения'
            ];
        }
        $messageModel->delete();
        return [
            'success' => true,
            'message' => "Сообщение удалено"
        ];
    }

    /**
     * Prepare a file to be sent
     * 
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function sendFilesInConversation(Request $request)
    {
        return Chat::sendFiles($request->input('conversationId') , $request->file('files'));
    }
}
