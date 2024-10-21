<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';

    protected $fillable = ['name', 'value', 'default', 'type', 'min', 'max'];

    protected $casts = [
        'options' => 'array'
    ];
}
