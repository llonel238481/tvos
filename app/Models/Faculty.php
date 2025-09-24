<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
    'facultyname',
    'email',
    'contact',
    ];

    public function travelLists()
    {
        return $this->hasMany(Travel_Lists::class, 'faculty_id');
    }
}
