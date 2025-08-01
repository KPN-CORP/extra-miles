<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyParticipant extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class, 'form_id', 'id');
    }
}
