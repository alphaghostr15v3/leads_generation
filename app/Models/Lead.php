<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_name',
        'address',
        'city',
        'state',
        'phone',
        'website',
        'review',
    ];
}
