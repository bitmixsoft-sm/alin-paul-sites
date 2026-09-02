<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unsubscribe extends Model
{
	protected $table = 'unsubscribed';
	protected $connection= 'chats';
    protected $guarded = [];
}
