<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Forecast;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class ParseForecasts extends Command
{
    protected $signature = 'parse:forecasts';

    protected $description = 'Parse info from site';

    public function handle(): void
    {
        $response = (new Client())->request('GET', config('app.link_for_parse'), [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'cookies' => new CookieJar()
        ]);

        $htmlContent = (string)$response->getBody();
        $crawler = new Crawler($htmlContent);
        if ($crawler->filter('.forecast-preview')->count() == 0) {
            $this->error('No forecast-preview elements found.');
            return;
        }

        $crawler->filter('.forecast-preview')->each(function ($node) {
            $profit = $node->filter('.forecast-preview__author-stat-item .is-up-color')->last()->text();
            if (!str_contains($profit, '%') || (float) $profit < 25) {
//                return;
            }

            $sportType = $node->filter('.forecast-preview__league')->first()->text();
            foreach (['Баскетбол', 'Волейбол', 'КХЛ', 'Настольный теннис'] as $value) {
                if (str_contains($sportType, $value)) {
                    return;
                }
            }

            $lastResults = '';
            $validateLastResults = '';
            $node->filter('.forecast-preview__author-results span')
                ->each(function ($spanNode) use (&$lastResults, &$validateLastResults) {
                    $classes = explode(' ', $spanNode->attr('class'));
                    $result = [
                        'is-up' => '1',
                        'is-default' => '0',
                        'is-down' => '-1',
                    ][end($classes)];

                    if ($lastResults && !$validateLastResults) {
                        $validateLastResults = $lastResults === '-1' || $result === '-1' ? 'fail' : 'success';
                    }

                    $lastResults .= $lastResults === '' ? $result :  " $result";
            });

            if ($validateLastResults === 'fail') {
                return;
            }

            $authorExplanation = '';
            if ($node->filter('.forecast-preview__text-inner')->count() > 0) {
                $authorExplanation = $node->filter('.forecast-preview__text-inner')->text();
            }

            if (Forecast::where('explanation', $authorExplanation)->exists()) {
                return;
            }

            $forecastDate = $node->filter('.forecast-preview__date')->text();
            $teams = $node->filter('.forecast-preview__teams')->text();
            $prediction = $node->filter('.forecast-preview__extra-bet-item-value.is-up-bg')->first()->text();
            $coefficient = $node
                ->filter('.forecast-preview__extra-bet-item:contains("Кф") .forecast-preview__extra-bet-item-value.is-up-bg')
                ->text();

            Forecast::create([
                'teams'        => $teams,
                'sport_type'   => $sportType,
                'prediction'   => $prediction,
                'date'         => $forecastDate,
                'last_results' => $lastResults,
                'profit'       => $profit,
                'coefficient'  => (float) $coefficient,
                'explanation'  => $authorExplanation,
            ]);

            $this->info("Forecast Date: $forecastDate, Last Results: $lastResults, Profit: $profit, " .
                "Sport Type: $sportType, Teams: $teams, Prediction: $prediction, Coefficient: $coefficient, " .
                "Explanation: $authorExplanation \n");
        });
    }
}
