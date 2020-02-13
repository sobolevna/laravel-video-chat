<?php

namespace Sobolevna\LaravelVideoChat\Services;

use Illuminate\Contracts\Config\Repository;
use Sobolevna\LaravelVideoChat\Repositories\ConversationRepository;
use Sobolevna\LaravelVideoChat\Models\{Conversation};

class Chat
{
    protected $config;

    protected $conversation;

    protected $userId;
    /**
     * @var GroupConversationRepository
     */
    protected $group;

    /**
     * Chat constructor.
     *
     * @param Repository                  $config
     * @param ConversationRepository      $conversation
     */
    public function __construct(
        Repository $config,
        ConversationRepository $conversation
    ) {
        $this->config = $config;
        $this->conversation = $conversation;
        $this->userId = check() ? check()->user()->id : null;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllConversations()
    {
        return $this->conversation->getAllConversations($this->userId);
    }

    /**
     * @param int $conversationId
     *
     * @return object
     */
    public function getConversationMessageById($conversationId)
    {
        if ($this->conversation->checkUserExists($this->userId, $conversationId)) {
            $channel = $this->getChannelName($conversationId, 'chat_room');

            return $this->conversation->getConversationMessageById($conversationId, $this->userId, $channel);
        }

        abort(404);
    }

    /**
     * @param int $conversationId
     * @param string $text
     */
    public function sendMessage($conversationId, $text)
    {
        $this->conversation->sendMessage($conversationId, [
            'text'    => $text,
            'user_id' => $this->userId,
            'channel' => $this->getChannelName($conversationId, 'chat_room'),
        ]);
    }

    /**
     * @param int $conversationId
     * @param array $data
     */
    public function startVideoCall($conversationId, array $data)
    {
        $channel = $this->getChannelName($conversationId, 'chat_room');
        $this->conversation->startVideoCall($data, $channel);
    }

    /**
     * @param int $userId
     */
    public function startConversationWith($userId)
    {
        $this->conversation->startConversationWith($this->userId, $userId);
    }

    /**
     * @param int $conversationId
     */
    public function acceptMessageRequest($conversationId)
    {
        $this->conversation->acceptMessageRequest($this->userId, $conversationId);
    }

    /**
     * @param int $conversationId
     * @param string $type
     *
     * @return string
     */
    private function getChannelName($conversationId, $type)
    {
        return $this->config->get('laravel-video-chat.channel.'.$type).'-'.$conversationId;
    }

    /**
     * @param int $conversationName
     * @param array $users
     */
    public function createConversation($conversationName, array $users)
    {
        $users[] = $this->userId;
        $this->conversation->createGroupConversation($conversationName, $users);
    }

    /**
     * @param int $conversationId
     * @param array $users
     */
    public function removeMembers($conversationId, array $users)
    {
        $this->conversation->removeMembers($conversationId, $users);
    }

    /**
     * @param int $conversationId
     * @param array $users
     */
    public function addMembers($conversationId, array $users)
    {
        $this->conversation->addMembers($conversationId, $users);
    }

    /**
     * @param int $conversationId
     */
    public function leaveConversation($conversationId)
    {
        $this->conversation->leaveConversation($conversationId, $this->userId);
    }

    /**
     * @param $conversationId
     * @param $file
     * @param $type
     */
    public function sendFiles($conversationId, $file, $type = 'conversation')
    {
        switch ($type) {
            case 'conversation':
            default:
                $ret = $this->conversation->sendMessage($conversationId, [
                    'file'    => $file,
                    'text'    => 'File Sent',
                    'user_id' => $this->userId,
                    'channel' => $this->getChannelName($conversationId, 'chat_room'),
                ]);
                break;
        }
        return $ret;
    }
    
    /**
     * 
     * @param string $conversationName
     * @param int $userId
     * @return Conversation
     */
    public function addParticipant($conversationName, $userId) {
        $conversation = Conversation::firstOrCreate(['name' => $conversationName]);
        $this->addMembers($conversation->id, [$userId]);
        return $conversation;
    }

}
