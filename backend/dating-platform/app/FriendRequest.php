<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
	protected $table = 'friend_requests';
    protected $guarded = [];

    public function userFromRelationship()
    {
        return $this->belongsTo(User::class, 'user_from');
    }

    public function userFrom()
    {
        return User::where('id', $this->user_from)->firstOrFail();
    }

}
