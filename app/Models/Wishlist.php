<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'item_type', 'item_id'];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'item_id');
    }
}
