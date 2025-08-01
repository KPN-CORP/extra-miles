<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;
    
    protected $connection = 'kpncorp';
    protected $table = 'password_reset_tokens';

    protected $fillable = [
        'email',
        'token',
    ];
}
