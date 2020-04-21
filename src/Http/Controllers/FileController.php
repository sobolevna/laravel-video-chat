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
            'messages' => $conversationModel->files
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
            return [
                'success' => false,
                'message' => 'У вас нет прав отправлять файлы в беседу'
            ];
        }
        $resultFileList = [];
        foreach ($files as $file) {
            $fileName = Carbon::now()->format('YmdHis').'-'.$file->getClientOriginalName();
            $path = $this->strFinish('', '/').$fileName;
            $content = File::get($file->getRealPath());
            $result = $this->manager->saveFile($path, $content);

            if ($result === true) {
                $resultFileList[] = $conversation->files()->create([
                    'message_id' => $messageId,
                    'name'       => $fileName,
                    'user_id'    => $userId,
                ]);
            }
        }
        return [
            'success' => true,
            'message' => "Файлы отправлены",
            'files' => $resultFileList
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
    public function destroy($conversation, $file) {
        $conversationModel = Conversation::findOrFail($conversation);
        if (!$conversationModel->users()->where('users.id', auth()->user()->id)->first()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав отправлять сообщения в беседу'
            ];
        }
        $fileModel = File::findOrFail($message);
        if ($fileModel->message->user_id != auth()->user()->id) {
            return [
                'success' => false,
                'message' => 'Вы не являетесь автором сообщения'
            ];
        }
        $fileModel->delete();
        return [
            'success' => true,
            'message' => "Файл удалён"
        ];
    }
}
