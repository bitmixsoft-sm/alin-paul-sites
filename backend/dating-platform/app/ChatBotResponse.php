<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatBotResponse extends Model
{
    protected $guarded = [];
    protected $connection= 'chats';
    protected $table = 'responses';
}
