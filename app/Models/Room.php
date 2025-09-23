<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'roomlist';
    protected $fillable = [
        'COL 1',  // Room Name
        'COL 2'   // Price
    ];

    protected $casts = [
        'COL 2' => 'decimal:2'
    ];

    // Accessor for room name
    public function getRoomNameAttribute()
    {
        return $this->{'COL 1'};
    }

    // Accessor for room price
    public function getRoomPriceAttribute()
    {
        return (float)$this->{'COL 2'};
    }

    // Format price for display
    public function getFormattedPriceAttribute()
    {
        return number_format((float)$this->{'COL 2'}, 2);
    }
}