<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTracking extends Model
{
    protected $guarded = [];
    protected $connection= 'chats';
    protected $table = 'email_tracking';
}
