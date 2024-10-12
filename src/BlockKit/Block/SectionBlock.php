<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\BlockKit\Block;

use DanBettles\SlackLogger\BlockKit\BlockInterface;
use DanBettles\SlackLogger\BlockKit\CompositionObject\TextObject;

use function array_filter;
use function array_map;

use const null;

/**
 * @internal
 */
class SectionBlock implements BlockInterface
{
    /**
     * @param TextObject[] $fields
     */
    public function __construct(
        private TextObject|null $text = null,
        private array $fields = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => 'section',
            'text' => $this->text?->toArray(),
            'fields' => array_map(
                fn (TextObject $textObj): array => $textObj->toArray(),
                $this->fields,
            ),
        ]);
    }
}
