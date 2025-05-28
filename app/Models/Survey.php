<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Survey extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['encrypted_id'];
    protected $hidden = ['id'];

    public function surveyParticipant()
    {
        return $this->hasMany(SurveyParticipant::class, 'survey_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
    }
}
