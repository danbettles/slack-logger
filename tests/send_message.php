<?php

use DanBettles\SlackLogger\Logger;

require __DIR__ . '/../vendor/autoload.php';

$logger = Logger::create(
    'Slack Logger Tests',
    '',  // @todo Change this!
);

$logger->info('A quick brown fox jumps over the lazy dog', [
    'extra' => 'Lorem ipsum dolor',
]);
