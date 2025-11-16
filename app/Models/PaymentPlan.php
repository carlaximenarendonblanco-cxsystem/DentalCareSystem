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
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
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
