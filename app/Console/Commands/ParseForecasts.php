<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Helpers\CalculationsHelper;
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
//                return;
            }

            $sportType = $node->filter('.forecast-preview__league')->first()->text();
            $typesForExclude = [
                'Волейбол', 'КХЛ', 'Настольный теннис', 'Желтые карточки', 'Лига Алеф', 'Чемпионат ОАЭ', 'Егип', 'Гана',
                'Бахрейн', 'Kontinental Hockey League', 'Чемпионат Бельгии. Премьер-лига', 'Киберспорт', 'Вброс аутов',
                'Фолы', 'Штрафное время',
            ];

            foreach ($typesForExclude as $value) {
                if (str_contains($sportType, $value)) {
                    return;
                }
            }

            $authorExplanation = '';
            if ($node->filter('.forecast-preview__text-inner')->count() > 0) {
                $authorExplanation = $node->filter('.forecast-preview__text-inner')->text();
            }

            if (Forecast::where('explanation', $authorExplanation)->exists()) {
                return;
            }

            $author = $node->filter('.forecast-preview__author-name')->text();
            $author = preg_replace('/\s\/\d+$/', '', $author);
            $url = config('app.url_author') . str_replace(' ', '+', $author);

            $authorResponse = (new Client())->request('GET', $url, $options);
            $authorHtmlContent = (string) $authorResponse->getBody();
            $authorCrawler = new Crawler($authorHtmlContent);

            $lastResults = '';
            $bets = [];
            $authorCrawler->filter('.user-series__item')
                ->each(function ($resultDiv) use (&$lastResults, &$bets) {
                    $classes = explode(' ', $resultDiv->attr('class'));
                    $coefficient = (float) $resultDiv->filter('.user-series__kf')->text();

                    if (end($classes) === 'is-win') {
                        $lastResults .= " 1";
                        array_unshift($bets, ['result' => 'win', 'coef' => $coefficient]);
                    } elseif (end($classes) === 'is-lose') {
                        $lastResults .= " -1";
                        array_unshift($bets, ['result' => 'loose', 'coef' => $coefficient]);
                    } else {
                        $lastResults .= " 0";
                        array_unshift($bets, ['result' => 'draw', 'coef' => $coefficient]);
                    }
            });

            $attractiveness = CalculationsHelper::attractiveness($bets);
            if ($attractiveness < 233) {
                return;
            }

            $forecastDate = $node->filter('.forecast-preview__date')->text();
            $teams = $node->filter('.forecast-preview__teams')->text();
            $prediction = $node->filter('.forecast-preview__extra-bet-item-value.is-up-bg')->first()->text();

            $data = [
                'teams'          => $teams,
                'sport_type'     => $sportType,
                'prediction'     => $prediction,
                'attractiveness' => $attractiveness,
                'date'           => $forecastDate,
                'last_results'   => $lastResults,
                'profit'         => $profit,
                'coefficient'    => $coefficient,
                'explanation'    => $authorExplanation,
                'author'         => $author,
                'author_link'    => $url
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
