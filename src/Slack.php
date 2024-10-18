<?php declare(strict_types=1);

namespace DanBettles\SlackLogger;

use DanBettles\SlackLogger\BlockKit\Block\ContextBlock;
use DanBettles\SlackLogger\BlockKit\Block\SectionBlock;
use DanBettles\SlackLogger\BlockKit\CompositionObject\TextObject;
use InvalidArgumentException;
use RuntimeException;
use Stringable;

use function array_filter;
use function array_key_exists;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function filter_var;
use function json_encode;
use function print_r;
use function sprintf;
use function ucfirst;

use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;
use const FILTER_VALIDATE_URL;
use const false;
use const null;
use const true;

/**
 * @phpstan-import-type ContextArray from Logger
 *
 * @phpstan-type NameValuePairs array<string,mixed>
 * @phpstan-type MessageArray array<string,mixed[]>
 * @phpstan-type ConstructorArgs array{string}
 * @phpstan-type CreateMessageArgs array{string,string|Stringable,ContextArray}
 */
class Slack
{
    /**
     * @throws InvalidArgumentException If the webhook URL is invalid
     */
    public function __construct(
        private string $webhookUrl,
    ) {
        if (!filter_var($this->webhookUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('The webhook URL is invalid');
        }
    }

    /**
     * @phpstan-param MessageArray|string $message
     * @throws RuntimeException If it failed to initialize a new cURL session
     * @throws RuntimeException If it failed to set all cURL options
     * @throws RuntimeException If it failed to send the message JSON to Slack
     * @throws RuntimeException If the message JSON was rejected by Slack
     */
    public function sendMessage(
        array|string $message,
        bool $messageIsJson = false,
    ): string {
        $curl = curl_init();

        if (false === $curl) {
            throw new RuntimeException('Failed to initialize a new cURL session');
        }

        $json = $message;

        if (!$messageIsJson) {
            /** @var string */
            $json = json_encode($message);
        }

        /** @var string $json */

        $allOptionsWereSet = curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_URL => $this->webhookUrl,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $json,
        ]);

        if (!$allOptionsWereSet) {
            throw new RuntimeException('Failed to set all cURL options');
        }

        /** @var string|false */
        $result = curl_exec($curl);

        if (false === $result) {
            throw new RuntimeException('Failed to send the message JSON to Slack');
        }

        /** @var int */
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        if (200 !== $responseCode) {
            throw new RuntimeException(<<<END
                The message JSON was rejected by Slack.

                **Response code**
                {$responseCode}

                **Transfer**
                {$result}

                **Request body**
                {$json}
                END);
        }

        return $result;
    }

    private function createDisplayValue(mixed $value): string
    {
        if (Utils::isStringable($value)) {
            /** @var Stringable $value */
            return (string) $value;
        }

        return sprintf(
            "```%s```",
            print_r($value, true)  // phpcs:ignore
        );
    }

    /**
     * @phpstan-param NameValuePairs $pairs
     * @return TextObject[]
     */
    private function createTextObjects(
        array $pairs,
        string $glue = ': ',
    ): array {
        $textObjects = [];

        foreach ($pairs as $name => $value) {
            $displayValue = $this->createDisplayValue($value);
            $textObjects[] = new TextObject("*{$name}*{$glue}{$displayValue}");
        }

        return $textObjects;
    }

    /**
     * @phpstan-param NameValuePairs $pairs
     */
    private function createTextFieldsBlock(array $pairs): SectionBlock|null
    {
        $nonEmpty = array_filter($pairs);

        if (!$nonEmpty) {
            return null;
        }

        return new SectionBlock(
            fields: $this->createTextObjects($nonEmpty, "\n")
        );
    }

    /**
     * Creates a Slack message in the form of a PHP array
     *
     * Notable excerpts from PSR-3:
     * - "A given value in the context MUST NOT throw an exception nor raise any php error, warning or notice."
     * - "If an Exception object is passed in the context data, it MUST be in the 'exception' key."
     * - "Implementors MUST still verify that the 'exception' key is actually an Exception before using it as such"
     *
     * @phpstan-param ContextArray $context
     * @phpstan-return MessageArray
     */
    public function createMessage(
        string $logLevel,
        string|Stringable $message,
        array $context = [],
    ): array {
        $blocks = [];

        $appContext = null;

        if (array_key_exists('appContext', $context) && $context['appContext'] instanceof AppContext) {
            $appContext = $context['appContext'];
            unset($context['appContext']);
        }

        if ($appContext) {
            $blocks[] = ['type' => 'divider'];

            $blocks[] = (new ContextBlock($this->createTextObjects([
                'App' => $appContext->getAppName(),
                'Hostname' => $appContext->getHostname(),
                'Script' => $appContext->getScriptName(),
            ])))->toArray();
        }

        $blocks[] = (new SectionBlock(new TextObject(sprintf(
            "*%s %s*: %s",
            LogLevel::getEmojiShortcodeForLevel($logLevel),
            ucfirst($logLevel),
            $message,
        ))))->toArray();

        if ($context) {
            $blocks[] = $this->createTextFieldsBlock($context)?->toArray();
        }

        if ($appContext) {
            $blocks[] = $this->createTextFieldsBlock([
                'Request URI' => $appContext->getRequestUri(),
                'Request Method' => $appContext->getRequestMethod(),
                '`$_POST`' => $appContext->getRequest(),
                'User Agent' => $appContext->getUserAgent(),
                'Referrer' => $appContext->getReferrer(),
            ])?->toArray();
        }

        return ['blocks' => array_filter($blocks)];
    }
}
