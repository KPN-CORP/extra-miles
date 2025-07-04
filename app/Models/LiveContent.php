<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveContent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title','content_link','created_by'
    ];
}
