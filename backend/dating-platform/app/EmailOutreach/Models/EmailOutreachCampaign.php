<?php

namespace App\EmailOutreach\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOutreachCampaign extends Model
{
    protected $table = 'email_outreach_campaigns';

    protected $fillable = ['account_id', 'name', 'base_language', 'abandon_after_days', 'restart_after_abandon', 'active'];

    public function account()
    {
        return $this->belongsTo(EmailOutreachAccount::class, 'account_id');
    }

    public function messages()
    {
        return $this->hasMany(EmailOutreachMessage::class, 'campaign_id')->orderBy('step_order');
    }

    public function recipients()
    {
        return $this->hasMany(EmailOutreachRecipient::class, 'campaign_id');
    }

    public function maxStep()
    {
        return (int) $this->messages()->max('step_order');
    }
}
