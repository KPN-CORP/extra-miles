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
    // protected $hidden = ['id'];

    public function eventParticipant()
    {
        return $this->hasMany(EventParticipant::class, 'event_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
    }

    protected $fillable = [
        'category', 'start_date', 'time_start', 'end_date', 'time_end','event_location',
        'title', 'description', 'image', 'logo', 'status', 'status_survey',
        'status_voting', 'quota', 'regist_deadline', 'businessUnit', 'unit','form_id','form_schema',
        'jobLevel', 'location', 'barcode_token','created_by','deleted_at'
    ];

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }
    public function formTemplates()
    {
        return $this->belongsTo(FormTemplate::class, 'form_id', 'id');
    }
}
