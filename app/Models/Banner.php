<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['img', 'alt_text'];

    // Relationships
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->img);
    }
}
