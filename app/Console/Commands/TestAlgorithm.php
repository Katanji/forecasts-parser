<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Helpers\CalculationsHelper;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;

class TestAlgorithm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:algorithm';

    protected $description = 'Command for test algorithm';

    #[NoReturn] public function handle()
    {
        // need 67+

        // must be good
        $winstreak6Total = CalculationsHelper::attractiveness([
            ['result' => 'loose', 'coef' => 2],
            ['result' => 'loose', 'coef' => 1.71],
            ['result' => 'loose', 'coef' => 1.72],
            ['result' => 'loose', 'coef' => 1.77],
            ['result' => 'win', 'coef' => 1.5],
            ['result' => 'win', 'coef' => 1.5],
            ['result' => 'win', 'coef' => 1.5],
            ['result' => 'win', 'coef' => 1.5],
            ['result' => 'win', 'coef' => 1.5],
            ['result' => 'win', 'coef' => 1.5],
        ]);

        // must be bad
        $winstreak4Total = CalculationsHelper::attractiveness([
            ['result' => 'win', 'coef' => 2],
            ['result' => 'loose', 'coef' => 2],
            ['result' => 'loose', 'coef' => 2],
            ['result' => 'loose', 'coef' => 2],
            ['result' => 'win', 'coef' => 2],
            ['result' => 'loose', 'coef' => 2],
            ['result' => 'win', 'coef' => 2],
            ['result' => 'win', 'coef' => 2],
            ['result' => 'win', 'coef' => 2],
            ['result' => 'win', 'coef' => 2],
        ]);

        // must be good
        $sanya_dokukin59Total = CalculationsHelper::attractiveness([
            ['result' => 'win', 'coef' => 1.76],
            ['result' => 'loose', 'coef' => null],
            ['result' => 'loose', 'coef' => null],
            ['result' => 'win', 'coef' => 1.53],
            ['result' => 'win', 'coef' => 2.67],
            ['result' => 'win', 'coef' => 2],
            ['result' => 'win', 'coef' => 1.71],
            ['result' => 'win', 'coef' => 1.72],
            ['result' => 'win', 'coef' => 1.77],
            ['result' => 'draw', 'coef' => null],
        ]);

        // must be good
        $chipolinoTotal = CalculationsHelper::attractiveness([
            ['result' => 'win', 'coef' => 4.1],
            ['result' => 'loose', 'coef' => null],
            ['result' => 'win', 'coef' => 4.21],
            ['result' => 'win', 'coef' => 4.55],
            ['result' => 'loose', 'coef' => 2.67],
            ['result' => 'win', 'coef' => 3.23],
            ['result' => 'loose', 'coef' => 1.71],
            ['result' => 'win', 'coef' => 3.25],
            ['result' => 'win', 'coef' => 3.08],
            ['result' => 'loose', 'coef' => null],
        ]);

        // must be bad
        $parkinson = CalculationsHelper::attractiveness([
            ['result' => 'win', 'coef' => 1.81],
            ['result' => 'loose', 'coef' => null],
            ['result' => 'win', 'coef' => 2.18],
            ['result' => 'loose', 'coef' => 4.55],
            ['result' => 'loose', 'coef' => 2.67],
            ['result' => 'win', 'coef' => 1.82],
            ['result' => 'loose', 'coef' => 1.71],
            ['result' => 'win', 'coef' => 1.82],
            ['result' => 'win', 'coef' => 1.86],
            ['result' => 'win', 'coef' => 1.77],
        ]);

        // must be bad
        $SabjiK1987 = CalculationsHelper::attractiveness([
            ['result' => 'draw', 'coef' => 1.81],
            ['result' => 'win', 'coef' => 1.87],
            ['result' => 'draw', 'coef' => 2.18],
            ['result' => 'win', 'coef' => 1.79],
            ['result' => 'loose', 'coef' => 2.67],
            ['result' => 'win', 'coef' => 1.75],
            ['result' => 'win', 'coef' => 2.01],
            ['result' => 'win', 'coef' => 1.69],
            ['result' => 'loose', 'coef' => 1.86],
            ['result' => 'win', 'coef' => 1.7],
        ]);

        dd([
            '$winstreak5Total good' => $winstreak6Total,
            '$winstreak4Total bad' => $winstreak4Total,
            '$chipolinoTotal good' => $chipolinoTotal,
            '$sanya_dokukin59Total good' => $sanya_dokukin59Total,
            '$parkinson bad' => $parkinson,
            '$SabjiK1987 bad' => $SabjiK1987,
        ]);
    }
}
