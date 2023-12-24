<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Forecast create(...$args)
 */
class Forecast extends Model
{
    use HasFactory;

    protected $fillable = ['teams', 'sport_type', 'prediction', 'date', 'last_results', 'profit', 'coefficient',
        'explanation'];
}
