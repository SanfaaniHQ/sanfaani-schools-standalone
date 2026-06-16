<?php

namespace App\Notifications;

use App\Models\PortalConversation;
use App\Models\PortalMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PortalMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private PortalConversation $conversation,
        private PortalMessage $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New portal message',
            'body' => str($this->message->body)->limit(120)->toString(),
            'action_url' => route('portal.conversations.show', ['conversationId' => $this->conversation->id]),
            'conversation_id' => $this->conversation->id,
            'message_id' => $this->message->id,
        ];
    }
}
