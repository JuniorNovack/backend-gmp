<?php

namespace App\Utils;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;
use function array_slice;

class ElasticSearchFormatter extends NormalizerFormatter
{
    public function format(LogRecord $record): mixed
    {
        $date = $record['datetime'];
        $current_request = app()->request;

        $exception = $record['context']['exception'];
        $trace = $trace = array_slice(explode("\n", $exception->getTraceAsString()), 0, 15);

        $formInput = [
            "email" => request()->get('email'),
            "ip" => request()->getClientIp(),
            "password" => "**********"
        ];

        $payload = request()->get('email') !== null ? json_encode($formInput) : $current_request->getContent();

        $output = [
            "exception" => get_class($exception),
            "date" => $date->format('Y-m-d\TH:i:s.v'),
            "message" => $exception->getMessage(),
            "method" => $current_request->getMethod(),
            "payload" => $payload,
            "url" => [
                "full" => request()->getUri()
            ],
            "stacktrace" => implode(PHP_EOL, $trace)
        ];

        return json_encode($output) . "\n";
    }
}

