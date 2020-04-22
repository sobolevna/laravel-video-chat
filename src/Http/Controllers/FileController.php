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
use Sobolevna\LaravelVideoChat\Models\{Conversation, File};

/**
 * @todo Логику перенести в сервисные классы
 */
class FileController extends Controller
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
            'files' => $conversation->files
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
            return [
                'success' => false,
                'message' => 'У вас нет прав отправлять файлы в беседу'
            ];
        }
        $files = $request->file('files');
        $resultFileList = [];
        foreach ($files as $file) {
            $resultFileList[] = Chat::saveFile($conversation, $file, auth()->user()->id, $request->get('messageId', 0));
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
    public function destroy(Conversation $conversation, File $file) {
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
