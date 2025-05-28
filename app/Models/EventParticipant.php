<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\SoftDeletes;
=======
>>>>>>> 6ad6b0c67ed9c25b2bfe98e8b37687c0300fc0ab

class EventParticipant extends Model
{
    use HasFactory;
<<<<<<< HEAD
    use SoftDeletes;
=======

    protected $fillable = [
        'event_id',
        'employee_id',
        'fullname',
        'business_unit',
        'job_level',
        'location',
        'status',
        'status',
        'attending_status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
>>>>>>> 6ad6b0c67ed9c25b2bfe98e8b37687c0300fc0ab
}
