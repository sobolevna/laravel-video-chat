<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\{Request,Response};
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Http\Resources\MessageResource;
use Illuminate\Routing\Controller;
use Storage;
use Sobolevna\LaravelVideoChat\Models\{Conversation, Message, File};
use SquareetLabs\LaravelOpenVidu\{OpenVidu, SignalProperties};

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
    public function index(Request $request) {
        $request->validate([
            'conversation_id' => 'exists:conversations,id'
        ]);
        $paginator = Message::when($request->get('conversation_id'), function($query) use ($request) {
            $query->where('conversation_id', $request->get('conversation_id'));
        })->with('sender.profile')->orderBy('created_at', 'desc')->paginate($request->get('per_page', 50));

        $items = $paginator->items();
        
        return [
            'success' => true,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'pages' => $paginator->lastItem(),
            'items' => !empty($items) ? MessageResource::collection($items) : [],
            'total' => $paginator->total()
        ];
    }

    /**
     * Send a message
     * 
     * @param int $conversation
     * @param Request
     * @return Response
     */
    public function store(Request $request, OpenVidu $manager)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id'
        ]);
        $conversation = Conversation::find($request->get('conversation_id'));
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

        $resource = new MessageResource($message);
        $manager->sendSignal(new SignalProperties($conversation->id, json_encode($resource), 'signal:message'));
        
        return response()->json([
            'success' => true,
            'message' => $resource
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
    public function update(Message $message, Request $request) {
        $conversation = $message->conversation;;
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
    public function destroy(Message $message) {
        $conversation = $message->conversation;
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
