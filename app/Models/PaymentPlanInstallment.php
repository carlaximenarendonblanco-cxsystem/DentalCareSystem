<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }
    
}
