<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Forecast create(...$args)
 */
class Forecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'attractiveness', 'author', 'coefficient', 'date','explanation', 'last_results', 'prediction', 'profit',
        'sport_type', 'teams',
    ];
}
