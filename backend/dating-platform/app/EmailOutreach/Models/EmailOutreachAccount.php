<?php

namespace App\EmailOutreach\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * A single sending mailbox (one per site, per the client's request - "fiecare site are mailul
 * lui de pe care se trimit reclamele"). smtp_password/imap_password are encrypted at rest the
 * same way AISetting.php's api keys are (Crypt::encryptString/decryptString) - plain Eloquent
 * accessors/mutators instead of an 'encrypted' cast so this still works on Laravel 5.7, which
 * doesn't have that cast type.
 */
class EmailOutreachAccount extends Model
{
    protected $table = 'email_outreach_accounts';

    protected $fillable = [
        'label', 'from_email', 'from_name',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
        'imap_host', 'imap_port', 'imap_username', 'imap_password',
        'daily_send_limit', 'openai_api_key',
    ];

    public function setOpenaiApiKeyAttribute($value)
    {
        $this->attributes['openai_api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getOpenaiApiKeyAttribute($value)
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setSmtpPasswordAttribute($value)
    {
        $this->attributes['smtp_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSmtpPasswordAttribute($value)
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setImapPasswordAttribute($value)
    {
        $this->attributes['imap_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getImapPasswordAttribute($value)
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function campaigns()
    {
        return $this->hasMany(EmailOutreachCampaign::class, 'account_id');
    }
}
