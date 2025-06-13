<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsView extends Model
{
    protected $fillable = ['news_id', 'employee_id', 'ip_address', 'user_agent'];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
