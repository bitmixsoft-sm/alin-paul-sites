<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $guarded = [];

    public function images()
    {
    	$images = $this->belongsToMany('App\ImageGet', 'image_album', 'album_id', 'image_id')->withTimestamps();
        return $images;
    }
}
