<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Helpers\CalculationsHelper;
use App\Mail\ErrorEmail;
use App\Mail\NewBetEmail;
use App\Models\Forecast;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\DomCrawler\Crawler;

class ParseForecasts extends Command
{
    protected $signature = 'parse:forecasts';

    protected $description = 'Parse info from site';

    private array $typesForExclude = [
        'Волейбол', 'КХЛ', 'Настольный теннис', 'Желтые карточки', 'Лига Алеф', 'Чемпионат ОАЭ', 'Егип', 'Гана',
        'Бахрейн', 'Kontinental Hockey League', 'Чемпионат Бельгии. Премьер-лига', 'Киберспорт', 'Вброс аутов', 'Фолы',
        'Штрафное время', 'Броски в створ ворот', 'Германия. Мужчины. Pro A', 'Finland - Korisliiga', '2-я Бундеслига. ProA',
        'Голы в большинстве', 'МХЛ', 'Lebanese Basketball League'
    ];

    public function handle(): void
    {
        try {
            $options = [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ],
                'cookies' => new CookieJar()
            ];

            self::handleForecasts($options);
            self::handleExpresses($options);
        } catch (GuzzleException|Exception $e) {
            info($e->getMessage());
            Mail::to('po6uh86@gmail.com')->send(new ErrorEmail($e->getMessage()));
        }
    }

    /**
     * @throws GuzzleException
     */
    private function handleForecasts($options): void
    {
        $crawler = self::getCrawler(config('app.url_forecasts'), $options);

        $crawler->filter('.forecast-preview')->each(/**
         * @throws GuzzleException
         */ function ($node) use ($options) {
            $sportType = $node->filter('.forecast-preview__league')->first()->text();
            foreach ($this->typesForExclude as $value) {
                if (str_contains($sportType, $value)) {
                    return;
                }
            }

            $validatedParams = $this->validatedParams($node);
            if (!$validatedParams) {
                return;
            }

            $coefficient = $validatedParams['coefficient'];
            $profit = $validatedParams['profit'];
            $authorExplanation = $validatedParams['authorExplanation'];

            $author = $node->filter('.forecast-preview__author-name')->text();
            $author = preg_replace('/\s\/\d+$/', '', $author);
            $url = config('app.url_author') . str_replace(' ', '+', $author);
            $parsedAuthorData = self::parseAuthorCrawler($url, $options);
            $bets = $parsedAuthorData['bets'];
            $lastResults = $parsedAuthorData['lastResults'];

            $attractiveness = CalculationsHelper::attractiveness($bets);
            if ($attractiveness < 267) {
                return;
            }

            $forecastDate = $node->filter('.forecast-preview__date')->text();
            $teams = $node->filter('.forecast-preview__teams')->text();
            $prediction = $node->filter('.forecast-preview__extra-bet-item-value.is-up-bg')->first()->text();

            $data = [
                'teams' => $teams,
                'sport_type' => $sportType,
                'prediction' => $prediction,
                'attractiveness' => $attractiveness,
                'date' => $forecastDate,
                'last_results' => $lastResults,
                'profit' => $profit,
                'coefficient' => $coefficient,
                'explanation' => $authorExplanation,
                'author' => $author,
                'author_link' => $url,
            ];

            Forecast::create($data);
            Mail::to('po6uh86@gmail.com')->send(new NewBetEmail($data));

            $result = "Forecast Date: $forecastDate, \n Last Results: $lastResults, \n Profit: $profit, \n" .
                "Sport Type: $sportType, \n Teams: $teams, \n Prediction: $prediction, \n Coefficient: $coefficient, \n" .
                "Explanation: $authorExplanation";

            $this->info("$result \n");
        });
    }

    private function handleExpresses($options): void
    {
        $crawler = self::getCrawler(config('app.url_expresses'), $options);

        $crawler->filter('.forecast-preview')->each(/**
         * @throws GuzzleException
         */ function ($node) use ($options) {

            $coefficient = (float)$node
                ->filter('.forecast-preview__extra-bet-item:contains("Итоговый кф") .forecast-preview__extra-bet-item-value.is-up-bg')
                ->text();

            if ($coefficient < 1.3) {
                return;
            }

            $profit = (int)round((float)$node->filter('.forecast-preview__author-stat-item span')->last()->text());
            if ($profit < 28) {
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
            $author = preg_replace('/\s\/\d+$/', '', $author);
            $url = config('app.url_author') . str_replace(' ', '+', $author);
            $parsedAuthorData = self::parseAuthorCrawler($url, $options);
            $bets = $parsedAuthorData['bets'];
            $lastResults = $parsedAuthorData['lastResults'];

            $attractiveness = CalculationsHelper::attractiveness($bets);
            if ($attractiveness < 267) {
                return;
            }

            $forecastDate = $node->filter('.forecast-preview__date')->text();
            $teams = $node->filter('.express-table')->text();

            $data = [
                'teams' => $teams,
                'attractiveness' => $attractiveness,
                'date' => $forecastDate,
                'last_results' => $lastResults,
                'profit' => $profit,
                'coefficient' => $coefficient,
                'explanation' => $authorExplanation,
                'author' => $author,
                'author_link' => $url,
            ];

            Forecast::create($data);
            Mail::to('po6uh86@gmail.com')->send(new NewBetEmail($data));

            $result = "Forecast Date: $forecastDate, \n Last Results: $lastResults, \n Profit: $profit, \n" .
                "Teams: $teams, \n Coefficient: $coefficient, \n" . "Explanation: $authorExplanation";

            $this->info("$result \n");
        });
    }

    /**
     * @throws GuzzleException
     */
    private static function getCrawler(string $url, array $options): Crawler
    {
        $response = (new Client())->request('GET', $url, $options);
        return new Crawler((string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    private static function parseAuthorCrawler(string $url, array $options): array
    {
        $lastResults = '';
        $bets = [];
        $authorCrawler = self::getCrawler($url, $options);

        $authorCrawler->filter('.user-series__item')->each(function ($resultDiv) use (&$lastResults, &$bets) {
            $classes = explode(' ', $resultDiv->attr('class'));
            $coefficient = (float)$resultDiv->filter('.user-series__kf')->text();

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

        return ['bets' => $bets, 'lastResults' => $lastResults];
    }

    private function validatedParams($node): ?array
    {
        $coefficient = (float)$node
            ->filter('.forecast-preview__extra-bet-item:contains("Кф") .forecast-preview__extra-bet-item-value.is-up-bg')
            ->text();
        $profit = (int)round((float)$node->filter('.forecast-preview__author-stat-item span')->last()->text());

        if ($coefficient < 1.3 || $profit < 28) {
            return null;
        }

        $authorExplanation = '';
        if ($node->filter('.forecast-preview__text-inner')->count() > 0) {
            $authorExplanation = $node->filter('.forecast-preview__text-inner')->text();
        }

        if (Forecast::where('explanation', $authorExplanation)->exists()) {
            return null;
        }

        return ['coefficient' => $coefficient, 'profit' => $profit, 'authorExplanation' => $authorExplanation];
    }
}
