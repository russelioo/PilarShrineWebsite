<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Determine whether the user can view the conversation.
     */
    public function view(User $actor, Conversation $conversation): bool
    {
        // Direct participation check
        if ($conversation->participants()->where('user_id', $actor->id)->exists()) {
            return true;
        }

        // Commission conversations: Parish-wide leadership or assigned commission members
        if ($conversation->isCommission()) {
            return $actor->canAccessCommission($conversation->commission_id);
        }

        // Ministry conversations: Parish-wide leadership or ministry members
        if ($conversation->isMinistry()) {
            return $actor->canAccessMinistry($conversation->ministry_id);
        }

        return false;
    }

    /**
     * Determine whether the user can send a message in the conversation.
     */
    public function sendMessage(User $actor, Conversation $conversation): bool
    {
        return $this->view($actor, $conversation);
    }
}

