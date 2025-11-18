<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function installments()
    {
        return $this->hasMany(PaymentPlanInstallment::class);
    }
    
}
