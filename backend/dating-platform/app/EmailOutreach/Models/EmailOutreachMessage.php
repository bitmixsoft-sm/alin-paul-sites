<?php

namespace App\EmailOutreach\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOutreachMessage extends Model
{
    protected $table = 'email_outreach_messages';

    protected $fillable = ['campaign_id', 'step_order', 'body', 'image_path'];

    public function campaign()
    {
        return $this->belongsTo(EmailOutreachCampaign::class, 'campaign_id');
    }
}
