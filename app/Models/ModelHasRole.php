<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelHasRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];
    
    protected $connection = 'kpncorp';
    protected $table = 'model_has_roles';

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
