<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
    'facultyname',
    'email',
    'contact',
    'department_id',
    'signature',
    'user_id',
    ];

     // Faculty belongs to a Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function travelLists()
    {
        return $this->hasMany(Travel_Lists::class, 'faculty_id');
    }
}
