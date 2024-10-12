# Slack Logger

A simple but effective [PSR-compliant](https://www.php-fig.org/psr/psr-3/) logger that sends log-entries to a [Slack incoming webhook](https://api.slack.com/messaging/webhooks).

## Basic Usage

Create an instance of the logger:

```php
use DanBettles\SlackLogger\Logger;

$logger = Logger::create(
    '<Name of host app>',
    '<Slack webhook URL>',
);
```

Create log-entries (as you would using any PSR-compliant logger):

```php
$logger->debug('Enemy tanks approaching');

$logger->emergency('Send out the patrol!', [
    'exception' => $ex,
]);
```

## References

- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Sending messages using incoming webhooks](https://api.slack.com/messaging/webhooks)
- [Slack Block Kit Builder](https://app.slack.com/block-kit-builder/)
- [Emoji Cheat Sheet](https://www.webfx.com/tools/emoji-cheat-sheet/)
