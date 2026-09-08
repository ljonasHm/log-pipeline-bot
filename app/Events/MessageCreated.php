<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Message $message){}
}
