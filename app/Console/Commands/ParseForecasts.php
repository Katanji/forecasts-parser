<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\NewBetEmail;
use App\Models\Forecast;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\DomCrawler\Crawler;

class ParseForecasts extends Command
{
    protected $signature = 'parse:forecasts';

    protected $description = 'Parse info from site';

    public function handle(): void
    {
//        @todo add analyzer for legs

        $options = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'cookies' => new CookieJar()
        ];

        $response = (new Client())->request('GET', config('app.url_forecasts'), $options);

        $htmlContent = (string)$response->getBody();
        $crawler = new Crawler($htmlContent);
        if ($crawler->filter('.forecast-preview')->count() == 0) {
            $this->error('No forecast-preview elements found.');
            return;
        }

        $crawler->filter('.forecast-preview')->each(function ($node) use ($options) {
            $coefficient = (float) $node
                ->filter('.forecast-preview__extra-bet-item:contains("Кф") .forecast-preview__extra-bet-item-value.is-up-bg')
                ->text();

            if ($coefficient < 1.3) {
                return;
            }

            $profit = (int) round((float) $node->filter('.forecast-preview__author-stat-item span')->last()->text());
            if ($profit < 28) {
                return;
            }

            $sportType = $node->filter('.forecast-preview__league')->first()->text();
            $typesForExclude = [
                'Волейбол', 'КХЛ', 'Настольный теннис', 'Желтые карточки', 'Лига Алеф', 'Чемпионат ОАЭ', 'Егип', 'Гана',
                'Бахрейн', 'Kontinental Hockey League', 'Чемпионат Бельгии. Премьер-лига', 'Киберспорт'
            ];

            foreach ($typesForExclude as $value) {
                if (str_contains($sportType, $value)) {
                    return;
                }
            }

            // @todo add filter by last 10 results
            $lastResults = '';
            $node->filter('.forecast-preview__author-results span')
                ->each(function ($spanNode) use (&$lastResults, &$validateLastResults) {
                    $classes = explode(' ', $spanNode->attr('class'));
                    $result = [
                        'is-up' => '1',
                        'is-default' => '0',
                        'is-down' => '-1',
                    ][end($classes)];

                    $lastResults .= $lastResults === '' ? $result :  " $result";
            });

            if (!strlen($lastResults) || str_contains($lastResults, '-')) {
                return;
            }

            $authorExplanation = '';
            if ($node->filter('.forecast-preview__text-inner')->count() > 0) {
                $authorExplanation = $node->filter('.forecast-preview__text-inner')->text();
            }

            if (Forecast::where('explanation', $authorExplanation)->exists()) {
                return;
            }

            $author = $node->filter('.forecast-preview__author-name')->text();
            $url = config('app.url_author') . str_replace(' ', '+', $author);
            $authorResponse = (new Client())->request('GET', $url, $options);
            $authorHtmlContent = (string) $authorResponse->getBody();
            $authorCrawler = new Crawler($authorHtmlContent);

            $authorCrawler->filter('.user-series__item')->each(function ($resultDiv, $index) use (&$lastResults) {
                if ($index < 5) {
                    return;
                }

                $classes = explode(' ', $resultDiv->attr('class'));
                $result = [
                    'is-win' => '1',
                    'is-lose' => '-1',
                ][end($classes)] ?? '0';

                $lastResults .= " $result";
            });

            $forecastDate = $node->filter('.forecast-preview__date')->text();
            $teams = $node->filter('.forecast-preview__teams')->text();
            $prediction = $node->filter('.forecast-preview__extra-bet-item-value.is-up-bg')->first()->text();

            $data = [
                'teams'        => $teams,
                'sport_type'   => $sportType,
                'prediction'   => $prediction,
                'date'         => $forecastDate,
                'last_results' => $lastResults,
                'profit'       => $profit,
                'coefficient'  => $coefficient,
                'explanation'  => $authorExplanation,
                'author'       => $author,
            ];

            Forecast::create($data);
            Mail::to('po6uh86@gmail.com')->send(new NewBetEmail($data));

            $result = "Forecast Date: $forecastDate, \n Last Results: $lastResults, \n Profit: $profit, \n" .
                "Sport Type: $sportType, \n Teams: $teams, \n Prediction: $prediction, \n Coefficient: $coefficient, \n" .
                "Explanation: $authorExplanation";

            $this->info("$result \n");
        });
    }
}
