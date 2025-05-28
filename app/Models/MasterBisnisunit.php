<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBisnisunit extends Model
{
    use HasFactory;

    protected $table = 'master_bisnisunits';
    protected $connection = 'kpncorp';
}
