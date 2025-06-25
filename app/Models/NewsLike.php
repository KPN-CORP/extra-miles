<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsLike extends Model
{
    protected $fillable = ['news_id', 'employee_id'];
    protected $table = 'news_likes';

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
