<?php

namespace App\EmailOutreach\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOutreachRecipient extends Model
{
    protected $table = 'email_outreach_recipients';

    protected $fillable = [
        'campaign_id', 'user_id', 'email', 'language', 'status',
        'current_step', 'last_sent_at', 'last_engaged_at', 'abandoned_at', 'open_token',
    ];

    protected $dates = ['last_sent_at', 'last_engaged_at', 'abandoned_at'];

    public function campaign()
    {
        return $this->belongsTo(EmailOutreachCampaign::class, 'campaign_id');
    }

    public function logs()
    {
        return $this->hasMany(EmailOutreachLog::class, 'recipient_id')->orderBy('created_at', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
