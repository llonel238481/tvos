<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Travel_Lists extends Model
{
     protected $fillable = [
        'travel_date',
        'request',
        'purpose',
        'destination',
        'means',
        'status',
    ];
}
