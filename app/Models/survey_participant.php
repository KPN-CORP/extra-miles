<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class survey_participant extends Model
{
    use HasFactory;

    protected $table = 'survey_participants';

    protected $fillable = [
        'survey_id', 'fullname', 'employee_id', 'status', 'form_id', 'form_data', 'job_level',
        'unit', 'business_unit', 'location', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_at'
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

}
