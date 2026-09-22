<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContentUnlock extends Model
{
    protected $table = 'content_unlocks';
    protected $guarded = [];
    public $timestamps = false;

    const TYPE_IMAGE = 'image';
    const TYPE_ALBUM = 'album';
}
