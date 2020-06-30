<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Events\{
    VideoChatStart, VideoChatFinish
};
use Illuminate\Routing\Controller;
use Storage;
use Sobolevna\LaravelVideoChat\Models\{Conversation, Message, File};

/**
 * @todo Логику перенести в сервисные классы
 */
class FileController extends Controller
{
    /**
     * @param int $conversation 
     * @return Response
     */
    public function index(Request $request) {        
        $request->validate([
            'conversation_id' => 'exists:conversations,id',
            'user_id' => 'exists:users,id',
            'message_id' => 'exists:messages,id',
        ]);
        $paginator = File::when($request->get('conversation_id'), function($query) use ($request) {
            $query->where('conversation_id', $request->get('conversation_id'));
        })
            ->when($request->get('user_id'), function($query) use ($request) {
                $query->where('user_id', $request->get('user_id'));
            })
            ->when($request->get('message_id'), function($query) use ($request) {
                $query->where('message_id', $request->get('message_id'));
            })
            ->paginate($request->get('per_page', 50));
        
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
     * Send a message
     * 
     * @param int $conversation
     * @param Request
     * @return Response
     */
    public function store(Request $request)
    {   
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message_id' => 'exists:messages,id',
            'files' => 'array',
            'files.*' => 'file',
        ]);
        $conversation = Conversation::find($request->get('conversation_id'));
        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав отправлять файлы в беседу'
            ];
        }
        $files = $request->file('files');
        $resultFileList = [];
        foreach ($files as $file) {
            $resultFileList[] = Chat::saveFile($conversation, $file, auth()->user()->id, $request->get('message_id', 0));
        }
        return response()->json([
            'success' => true,
            'message' => "Файлы отправлены",
            'files' => $resultFileList
        ],201);
    }

    /**
     * Delete message
     * 
     * @param int $conversation
     * @param int $message
     * @param Request $request
     * @return Response
     */
    public function destroy(File $file, Request $request) {           
        
        $conversation = $file->message->conversation;

        if (!$conversation->users()->where('users.id', auth()->user()->id)->first()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ], 403);
        }
        if ($file->message->user_id != auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не являетесь автором сообщения'
            ], 403);
        }
        if ($file->user_id != auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не являетесь отправителем файла'
            ], 403);
        }
        $file->delete();
        return [
            'success' => true,
            'message' => "Файл удалён"
        ];
    }
}
