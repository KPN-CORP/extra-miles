<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    use HasFactory;

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
}
