<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Clinic;
use App\Models\User;

class Treatment extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function getPaidAmountAttribute(){
        return $this->payments->sum('amount');
    }
    public function getRemainingAmountAttribute(){
        return $this->amount - $this->paid_amount;
    }
    public function patient():BelongsTo{
        return $this->belongsTo(Patient::class, 'ci_patient', 'ci_patient');
    }
        public function clinic()
    {
        return $this->belongsTo(Clinic::class);
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
