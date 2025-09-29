<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelRequestParty extends Model
{
    use HasFactory;

    protected $table = 'travel_request_parties'; // explicitly set table name

    protected $fillable = [
        'travel_list_id',
        'name',
    ];

    /**
     * Relationship: Each requesting party belongs to one travel list.
     */
    public function travelList()
    {
        return $this->belongsTo(Travel_Lists::class, 'travel_list_id');
    }
}
