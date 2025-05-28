<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
<<<<<<< HEAD
use Illuminate\Support\Facades\Crypt;
=======
>>>>>>> 6ad6b0c67ed9c25b2bfe98e8b37687c0300fc0ab

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

<<<<<<< HEAD
    protected $appends = ['encrypted_id'];
    protected $hidden = ['id'];

    public function eventParticipant()
    {
        return $this->hasMany(EventParticipant::class, 'event_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
=======
    protected $fillable = [
        'category', 'start_date', 'time_start', 'end_date', 'time_end',
        'title', 'description', 'image', 'logo', 'status', 'status_survey',
        'status_voting', 'quota', 'regist_deadline', 'businessUnit', 'unit',
        'jobLevel', 'location', 'barcode_token','created_by','deleted_at'
    ];

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
>>>>>>> 6ad6b0c67ed9c25b2bfe98e8b37687c0300fc0ab
    }
}
