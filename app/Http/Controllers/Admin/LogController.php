<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LogService;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class LogController extends Controller
{
    public function showLaravelLog(): View
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            abort(404, 'Log file not found');
        }

        $content = File::get($path);
        $logs = LogService::parseLogs($content);

        return view('layouts.admin.logs', compact('logs'));
    }
}
