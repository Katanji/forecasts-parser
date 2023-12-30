<?php

namespace App\Http\Helpers;

class CalculationsHelper
{
    public static function attractiveness(array $bets): int
    {
        $total = 100;

        foreach ($bets as $index => $betData) {
//            $multiplier = [4 => 3, 5 => 4, 6 => 5, 7 => 6, 8 => 7, 9 => 8][$index] ?? 1;
            $multiplier = $index > 4 ? 10.5 : 1;
            if ($betData['result'] === 'win') {
                $total += ((($total / 20) * $betData['coef']) - ($total / 20)) * $multiplier;
            } elseif ($betData['result'] === 'loose') {
                $total -= ($total / 20) * $multiplier;
            }
        }

        return (int) round($total);
    }
}
