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
    // protected $hidden = ['id'];

    protected $fillable = [
        'category','title','event_id','form_id','form_schema','status','description','banner','icon','start_date','end_date','time_start','time_end','content_link','quota','businessUnit','unit','jobLevel','location','created_by'
    ];

    public function surveyParticipant()
    {
        return $this->hasMany(SurveyParticipant::class, 'survey_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
    }

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class, 'form_id', 'id');
    }
}
