<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\BlockKit\CompositionObject;

use DanBettles\SlackLogger\Arrayable;

/**
 * @internal
 */
class TextObject implements Arrayable
{
    final public const string TYPE_MRKDWN = 'mrkdwn';

    public function __construct(
        private string $text,
        private string $type = self::TYPE_MRKDWN,
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'text' => $this->text,
        ];
    }
}
