<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Travel_Lists extends Model
{
    protected $table = 'travel_lists';

     protected $fillable = [
        'employee_id', 
        'travel_from',
        'travel_to',
        'purpose',
        'destination',
        'transportation_id',
        'faculty_id', 
        'ceo_id',
        'conditionalities',
        'status',
        'supervisor_signature',   // ✅ this must be here
        'ceo_signature',  
    ];

     public function transportation()
    {
        return $this->belongsTo(Transportation::class, 'transportation_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function requestParties()
    {
        return $this->hasMany(TravelRequestParty::class, 'travel_list_id');
    }

    public function ceo()
    {
        return $this->belongsTo(CEO::class, 'ceo_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

}
