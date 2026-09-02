<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Friend extends Model
{
	protected $table = 'friends';
    protected $guarded = [];

}
