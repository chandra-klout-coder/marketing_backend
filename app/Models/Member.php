<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'company'
    ];
}
