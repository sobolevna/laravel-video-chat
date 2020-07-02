<?php

namespace Sobolevna\LaravelVideoChat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Sobolevna\LaravelVideoChat\Models\Message;

class MessageResourse extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        return [
            'id'=>$this->id,
            'text' => $this->text,
            'user_id' => $this->user_id,
            'sender' => [
                'first_name' => $this->sender->profile->first_name,
                'middle_name' => $this->sender->profile->middle_name,
                'last_name' => $this->sender->profile->last_name,
                'avatar' => $this->sender->profile->avatar,
            ],
            'created_at' => $this->created_at,
            
        ];
    }
}