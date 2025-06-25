<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class News extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        // Kolom-kolom lainnya,
        'category', 'title', 'publish_date', 'content', 'image', 'link', 'businessUnit', 'status', 'created_by', 'deleted_at'
    ];

    protected $appends = ['encrypted_id'];
    // protected $hidden = ['id'];

    public function newsLikes()
    {
        return $this->belongsTo(NewsLike::class, 'id', 'news_id');
    }
    public function newsViews()
    {
        return $this->belongsTo(NewsView::class, 'id', 'news_id');
    }

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
    }
}
