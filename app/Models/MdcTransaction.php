<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MdcTransaction extends Model
{
    protected $connection = 'kpncorp';
    protected $table = 'mdc_transactions';
    
    use HasFactory;

    protected $fillable = [
        'usage_id','employee_id','contribution_level_code','no_medic','no_invoice',
        'hospital_name','patient_name','disease','date','coverage_detail','period',
        'medical_type','balance','balance_uncoverage','balance_verif','balance_bpjs',
        'created_by','verif_by','verif_at','admin_notes','approved_by','approved_at',
        'submission_type','rejected_by','rejected_at','reject_info','status',
        'medical_proof','created_at'
    ];
}
