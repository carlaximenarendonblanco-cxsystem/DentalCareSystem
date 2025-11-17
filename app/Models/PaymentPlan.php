<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Clinic;
use App\Models\User;

class PaymentPlan extends Model
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edit_by');
    }
}
