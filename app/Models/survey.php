<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class survey extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'surveys';

    protected $fillable = [
        'category', 'start_date', 'time_start', 'end_date', 'time_end',
        'title', 'description', 'banner', 'icon', 'status', 'quota', 'related', 'quota', 'form_id','event_id',
        'businessUnit', 'unit', 'jobLevel', 'location', 'created_by', 'deleted_at'
    ];

    public function participants()
    {
        return $this->hasMany(survey_participant::class);
    }
}
