<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalLead extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'website',
        'review',
    ];
}
