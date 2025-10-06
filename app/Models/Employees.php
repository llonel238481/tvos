<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
     protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'extensionname',     
        'department_id',
        'email',
        'sex',
        'user_id',
    ];

    // Each employee is associated with one user account
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Each employee belongs to one department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function travelLists()
    {
        return $this->hasMany(Travel_Lists::class);
    }



}
