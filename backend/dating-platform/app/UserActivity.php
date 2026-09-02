<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserActivity extends Model
{
    protected $guarded = [];

    /**
     * Get the user of the activity event.
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Vizitator',
        ]);
    }
}
