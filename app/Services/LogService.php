<?php

namespace App\Services;

class LogService
{
    public static function parseLogs(string $content): array
    {
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(\n|\z)/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $logs = [];
        foreach ($matches as $match) {
            $logs[] = [
                'timestamp' => $match[1],
                'environment' => $match[2],
                'level' => $match[3],
                'message' => trim($match[4]),
            ];
        }

        return array_reverse($logs);
    }
}
