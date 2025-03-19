<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['recipe_name', 'status', 'ingredients'];
    protected $casts = [
        'ingredients' => 'array'
    ];
}
