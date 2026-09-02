<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WelcomeMessage extends Model
{
    protected $guarded = [];
    protected $connection= 'chats';
    protected $table = 'welcome_messages';
}
