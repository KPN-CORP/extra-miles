<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Social extends Model
{
    use HasFactory;

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'link', 'category', 'created_by', 'created_at'
    ];

    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->id);
    }
}
