<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employees;


class Department extends Model
{
    protected $fillable = [
        'departmentname',
    ];

    // One department has many employees
    public function employees()
    {
        return $this->hasMany(Employees::class);
    }

    // One department has many faculties
    public function faculties()
    {
        return $this->hasMany(Faculty::class);
    }
}
