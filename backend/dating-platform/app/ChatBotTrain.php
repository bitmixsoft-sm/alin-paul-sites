<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatBotTrain extends Model
{
    protected $guarded = [];
    protected $connection= 'chats';
    protected $table = 'train';
}
