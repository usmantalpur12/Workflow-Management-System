<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Description extends Model
{
    protected $fillable = [
        'content', 'describable_id', 'describable_type',
    ];

    public function describable()
    {
        return $this->morphTo();
    }
}
