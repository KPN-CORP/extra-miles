<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['encrypted_id'];
    protected $hidden = ['id'];

    public function eventParticipant()
    {
        return $this->hasMany(EventParticipant::class, 'event_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
    }
}
