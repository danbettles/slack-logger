<?php

use DanBettles\SlackLogger\AppContext;
use DanBettles\SlackLogger\LogLevel;
use DanBettles\SlackLogger\Slack;

require __DIR__ . '/../vendor/autoload.php';

$appContext = new AppContext(
    basename(__FILE__),
    (gethostname() ?: 'unknown'),
    $_SERVER,  // @phpstan-ignore-line
    $_POST,
);

$slack = new Slack('https://example.com/');

$messageJson = json_encode($slack->createMessage(
    LogLevel::WARNING,
    'A quick brown fox jumps over the lazy dog',
    [
        'appContext' => $appContext,
        'foo' => 'bar',
        'baz' => [
            'foo' => 'bar',
            'baz' => 'qux',
        ],
    ],
)) . "\n";

// phpcs:ignore
echo $messageJson;
