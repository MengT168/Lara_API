<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Attribute extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens , HasFactory, Notifiable;

    protected $table = 'attribute';

    protected $fillable = [
        'type',
        'value',
    ];
}
