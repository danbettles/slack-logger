<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\BlockKit\Block;

use DanBettles\SlackLogger\BlockKit\BlockInterface;
use DanBettles\SlackLogger\BlockKit\CompositionObject\TextObject;

use function array_map;

/**
 * @internal
 */
class ContextBlock implements BlockInterface
{
    /**
     * @param TextObject[] $elements
     */
    public function __construct(
        private array $elements,
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'context',
            'elements' => array_map(
                fn (TextObject $compositionObj): array => $compositionObj->toArray(),
                $this->elements,
            ),
        ];
    }
}
