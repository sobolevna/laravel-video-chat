<?php

namespace Sobolevna\LaravelVideoChat\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Sobolevna\LaravelVideoChat\Events\NewConversationMessage;
use Sobolevna\LaravelVideoChat\Events\VideoChatStart;
use Sobolevna\LaravelVideoChat\Models\Conversation;
use Sobolevna\LaravelVideoChat\Repositories\BaseRepository;
use Sobolevna\LaravelVideoChat\Services\UploadManager;

class ConversationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Conversation::class;
    /**
     * @var UploadManager
     */
    private $manager;

    /**
     * ConversationRepository constructor.
     *
     * @param UploadManager $manager
     */
    public function __construct(UploadManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $user
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllConversations($user)
    {
        $conversations = $this->query()->with([
            'messages' => function ($query) {
                return $query->latest();
            }, 
            'users'
        ])->whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user);
        })->get();

        return $conversations;
    }

    /**
     * @param $user
     * @param $conversationId
     *
     * @return bool
     */
    public function canJoinConversation($user, $conversationId)
    {
        $group = $this->find($conversationId);

        if ($group) {
            foreach ($group->users()->get() as $member) {
                if ($member->id == $user->id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $conversationId
     * @param $userID
     * @param $channel
     *
     * @return object
     */
    public function getConversationMessageById($conversationId, $channel = null)
    {
        $conversation = $this->query()->with(['messages', 'messages.sender', 'messages.sender.profile', 'messages.files', 'users', 'users.profile', 'files'])->find($conversationId);

        $collection = (object) null;
        $collection->id = $conversationId;
        $collection->name = $conversation->name;
        $collection->channel_name = $channel;
        $collection->users = $conversation->users;
        $collection->messages = $conversation->messages;
        $collection->files = $conversation->files;
        
        return collect($collection);
    }
    
    /**
     * 
     * @param array $users
     * @param string $name
     * @param string $type
     * 
     * @return bool
     */
    public function createConversation(array $users, $name = null, $type = null)
    {
        $group = $this->query()->create([
            'name' => $name,
            'type' => $type
        ]);

        if ($group) {
            $group->users()->attach($users);

            return true;
        }

        return false;
    }
    
    /**
     * @param $conversationId
     * @param array $users
     *
     * @return bool
     */
    public function addMembers($conversationId, array $users)
    {
        $group = $this->find($conversationId);
                
        if (!$group) {
            return false;
        }
        
        $existingUsers = $group->users()->whereIn('users.id', $users)->get()->pluck('id');
        $usersToAdd = collect($users)->diff($existingUsers);
        if ($usersToAdd->isEmpty()) {
            return true;
        }

        $group->users()->attach($usersToAdd);
        return true;        
    }
    
    /**
     * @param $conversationId
     * @param array $users
     *
     * @return bool
     */
    public function removeMembers($conversationId, array $users)
    {
        $group = $this->find($conversationId);

        if ($group) {
            $group->users()->detach($users);

            return true;
        }

        return false;
    }
    
    /**
     * @param $conversationId
     * @param $userId
     *
     * @return bool
     */
    public function leaveConversation($conversationId, $userId)
    {
        $group = $this->find($conversationId);

        if ($group) {
            $group->users()->detach($userId);

            return true;
        }

        return false;
    }

    /**
     * @param $conversationId
     * @param array $data
     *
     * @return bool
     */
    public function sendConversationMessage($conversationId, array $data)
    {
        return $this->sendMessage($conversationId, $data);
    }

    /**
     * @param array $data
     * @param $channel
     */
    public function startVideoCall(array $data, $channel)
    {
        broadcast(new VideoChatStart($data, $channel));
    }


    /**
     * @param $userId
     * @param $conversationId
     *
     * @return bool
     */
    public function checkUserExists($userId, $conversationId)
    {
        $group = $this->find($conversationId);

        if ($group) {
            foreach ($group->users()->get() as $member) {
                if ($member->id == $userId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $conversationId
     * @param array $data
     *
     * @return array
     */
    public function sendMessage($conversationId, array $data)
    {
        $conversation = $this->find($conversationId);

        $created = $conversation->messages()
            ->create([
                'text'    => $data['text'],
                'user_id' => $data['user_id'],
            ]);

        if (!$created) {
            return false;
        }

        if (!empty($data['file'])) {
            $this->createFiles($data['file'], $conversation, $created->id, $data['user_id']);
        }

        $data['files'] = $conversation->messages()->find($created->id)->files()->get();

        broadcast(new NewConversationMessage($data['text'], $data['channel'], $data['files']));

        return $data;        
        
    }
    
    /**
     * 
     * @param array $files
     * @param Conversation $conversation
     * @param int $messageId
     * @param int $userId
     */
    protected function createFiles(array $files, $conversation, $messageId, $userId) {
        foreach ($files as $file) {
            $fileName = Carbon::now()->format('YmdHis').'-'.$file->getClientOriginalName();
            $path = $this->strFinish('', '/').$fileName;
            $content = File::get($file->getRealPath());
            $result = $this->manager->saveFile($path, $content);

            if ($result === true) {
                $conversation->files()->create([
                    'message_id' => $messageId,
                    'name'       => $fileName,
                    'user_id'    => $userId,
                ]);
            }
        }
    }
    
    /**
     * 
     * @param string $string
     * @param string $finish
     * @return string
     */
    protected function strFinish($string, $finish) {
        if (function_exists('str_finish')) {
            return str_finish($string, $finish);
        }
        
        return Illuminate\Support\Str::finish($string, $finish);
    }
}
