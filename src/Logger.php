<?php declare(strict_types=1);

namespace DanBettles\SlackLogger;

use Psr\Log\AbstractLogger;
use Stringable;

use function array_replace;
use function gethostname;

/**
 * A logger that sends log-entries to Slack.
 *
 * A message will be sent to Slack only if its log-level meets or exceeds the minimum log-level of the logger.  The
 * default minimum log-level of the logger is `"debug"` to ensure that, out of the box, the logger will send all
 * messages to Slack.
 *
 * @phpstan-import-type ServerArray from AppContext
 *
 * @phpstan-type OptionsArray array{minLogLevel?:string}
 * @phpstan-type ContextArray array<string,mixed>
 * @phpstan-type CreateArgs array{string,string,OptionsArray}
 * @phpstan-type ConstructorArgs array{AppContext,Slack,OptionsArray}
 * @phpstan-type LogArgs array{string,string|Stringable,ContextArray}
 */
class Logger extends AbstractLogger
{
    private int $minLogLevelPriority;

    /**
     * For convenience
     *
     * @phpstan-param OptionsArray $options
     */
    public static function create(
        string $appName,
        string $webhookUrl,
        array $options = [],
    ): self {
        $hostname = gethostname() ?: 'unknown';
        /** @phpstan-var ServerArray */
        $server = $_SERVER;

        return new self(
            new AppContext($appName, $hostname, $server, $_POST),
            new Slack($webhookUrl),
            $options,
        );
    }

    /**
     * @phpstan-param OptionsArray $options
     */
    public function __construct(
        private AppContext $appContext,
        private Slack $slack,
        private array $options = [],
    ) {
        $this->options = array_replace([
            'minLogLevel' => LogLevel::getLevelWithLowestPriority(),
        ], $this->options);

        LogLevel::assertLevelExists($this->options['minLogLevel']);

        /** @var int */
        $minLogLevelPriority = LogLevel::getPriorityOfLevel($this->options['minLogLevel']);
        $this->minLogLevelPriority = $minLogLevelPriority;
    }

    /**
     * @override
     * @param string $level
     * @phpstan-param ContextArray $context
     */
    public function log(
        $level,
        string|Stringable $message,
        array $context = [],
    ): void {
        LogLevel::assertLevelExists($level);

        /** @var int */
        $logLevelPriority = LogLevel::getPriorityOfLevel($level);

        if ($logLevelPriority < $this->minLogLevelPriority) {
            return;
        }

        $context = array_replace(['appContext' => $this->appContext], $context);
        $messageArray = $this->slack->createMessage($level, $message, $context);
        $this->slack->sendMessage($messageArray);
    }
}
