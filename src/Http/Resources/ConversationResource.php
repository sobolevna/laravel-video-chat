<?php

namespace Sobolevna\LaravelVideoChat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        self::withoutWrapping();
        return [
            'id'=>$this->id,
            'name' => $this->name,
            'users' => $this->whenLoaded('users'),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at,            
        ];
    }
}