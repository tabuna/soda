<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 *
 * @param array{blockLines: list<list<string>>, blocks: list<int>, nBlank: int, nLines: int, shortBlocks: int, totalLines: int} $data
 */
final readonly class LineBlockData
{
    /**
     * @param array{blockLines: list<list<string>>, blocks: list<int>, nBlank: int, nLines: int, shortBlocks: int, totalLines: int} $data
     */
    public function __construct(
        /** @var array{blockLines: list<list<string>>, blocks: list<int>, nBlank: int, nLines: int, shortBlocks: int, totalLines: int} */
        public array $data,
    ) {}

    public function nBlank(): int
    {
        return $this->data['nBlank'];
    }

    public function nLines(): int
    {
        return $this->data['nLines'];
    }

    public function totalLines(): int
    {
        return $this->data['totalLines'];
    }

    /**
     * @return list<int>
     */
    public function blocks(): array
    {
        return $this->data['blocks'];
    }

    /**
     * @return list<list<string>>
     */
    public function blockLines(): array
    {
        return $this->data['blockLines'];
    }

    public function shortBlocks(): int
    {
        return $this->data['shortBlocks'];
    }
}
