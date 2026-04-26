<?php

namespace Tests\Feature\Logging;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;

class LogHelper
{
    public static $interceptedLogs = [];

    public static function interceptLogs()
    {
        self::$interceptedLogs = [];
        Event::listen(MessageLogged::class, function (MessageLogged $e) {
            $context = $e->context;
            
            // Decodes if stringified JSON
            if (empty($context) && (str_starts_with($e->message, '{') || str_contains($e->message, '"method"'))) {
                $decoded = json_decode($e->message, true);
                if (is_array($decoded)) {
                    $context = array_merge($context, $decoded);
                }
            }

            self::$interceptedLogs[] = $context;
        });
    }

    public static function flushDatabaseLogs()
    {
        if (Schema::hasTable('request_logs')) DB::table('request_logs')->truncate();
        if (Schema::hasTable('api_logs')) DB::table('api_logs')->truncate();
        if (Schema::hasTable('logs')) DB::table('logs')->truncate();
    }

    public static function getAllLogs()
    {
        if (Schema::hasTable('request_logs')) {
            return DB::table('request_logs')->get()->map(fn($item) => (array)$item)->toArray();
        }
        if (Schema::hasTable('api_logs')) {
            return DB::table('api_logs')->get()->map(fn($item) => (array)$item)->toArray();
        }
        return self::$interceptedLogs;
    }

    public static function assertLogExistsForRequest($method, $endpoint, $status)
    {
        $logs = self::getAllLogs();
        $found = false;

        foreach ($logs as $log) {
            if (!empty($log) && 
                strtoupper($log['method'] ?? '') === strtoupper($method) &&
                str_contains($log['endpoint'] ?? '', $endpoint) &&
                (int)($log['status_code'] ?? 0) === (int)$status) {
                
                // Fields verification
                expect(isset($log['response_time']))->toBeTrue();
                expect((float)$log['response_time'])->toBeGreaterThan(0);
                
                $found = true;
                break;
            }
        }

        expect($found)->toBeTrue("Failed asserting that a $method log entry exists for $endpoint with status $status");
    }
}
