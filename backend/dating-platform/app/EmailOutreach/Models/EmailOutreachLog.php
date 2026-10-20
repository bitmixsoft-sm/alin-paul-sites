<?php

namespace App\EmailOutreach\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOutreachLog extends Model
{
    protected $table = 'email_outreach_logs';
    public $timestamps = true;
    const UPDATED_AT = null; // append-only log - never updated after creation

    protected $fillable = ['recipient_id', 'event', 'step'];

    public function recipient()
    {
        return $this->belongsTo(EmailOutreachRecipient::class, 'recipient_id');
    }
}
