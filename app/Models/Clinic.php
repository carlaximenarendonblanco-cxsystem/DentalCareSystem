<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function budgets()
    {
        return $this->hasMany(User::class);
    }
    public function events()
    {
        return $this->hasMany(User::class);
    }
    public function multimediaFiles()
    {
        return $this->hasMany(User::class);
    }
    public function patients()
    {
        return $this->hasMany(User::class);
    }
    public function payments()
    {
        return $this->hasMany(User::class);
    }
    public function paymentPlans()
    {
        return $this->hasMany(User::class);
    }
    public function treatments()
    {
        return $this->hasMany(User::class);
    }
    public function roomsList()
    {
        return range(1, $this->consulting_rooms);
    }
}
