<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Votes extends Model
{
    use HasFactory;

    protected $fillable = [
        'choice_id',
        'user_id',
        'poll_id',
        'division_id',
    ];
}
