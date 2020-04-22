<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\{Request,Response};
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use Storage;
use Sobolevna\LaravelVideoChat\Models\{Conversation, Message, File};

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
    public function index(Conversation $conversation) {
        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав добавлять участников в беседу'
            ];
        }
        return [
            'success' => true,
            'messages' => $conversation->messages()->with('files')->get()
        ];
    }

    /**
     * Send a message
     * 
     * @param int $conversation
     * @param Request
     * @return Response
     */
    public function store(Conversation $conversation, Request $request)
    {
        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ], 403);
        }
        $message = $conversation->messages()->create([
            'user_id'=>auth()->user()->id,
            'text'=>$request->get('text'),
        ]);

        $files = $request->file('files', null);
        if (!empty($files)) {
            foreach ($files as $file) {
                Chat::saveFile($conversation, $file, auth()->user()->id, $message->id);
            }
        }

        $fileList = $request->get('files', null);
        
        if (!empty($fileList)) {
            $fileIds = collect($fileList)->pluck('id');
            File::whereIn('id', $fileIds)->where('message_id', 0)->update(['message_id'=>$message->id]);            
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
    public function update(Conversation $conversation, Message $message, Request $request) {
        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ], 403);
        }
        if ($message->user_id != auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не являетесь автором сообщения'
            ], 403);
        }
        $message->text = $request->get('text');
        $message->save();

        $fileList = $request->get('files', null);
        
        if (!empty($fileList)) {
            $fileIds = collect($fileList)->pluck('id');
            File::whereIn('id', $fileIds)->where('message_id', 0)->update(['message_id'=>$message->id]);            
        }
        
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
    public function destroy(Conversation $conversation, Message $message) {
        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ], 403);
        }
        if ($message->user_id != auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не являетесь автором сообщения'
            ], 403);
        }
        $message->delete();
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
