<?php

namespace App\Service;

/** Résultat paginé d'une liste (affiché par partials/_pagination.html.twig). */
class Pagination implements \IteratorAggregate, \Countable
{
    public const LIGNES_PAR_PAGE = [10, 25, 50, 100, 500];

    /** @param array<int, mixed> $items */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $parPage,
        public readonly int $total,
    ) {
    }

    public function getPages(): int
    {
        return max(1, (int) ceil($this->total / $this->parPage));
    }

    public function getDebut(): int
    {
        return 0 === $this->total ? 0 : ($this->page - 1) * $this->parPage + 1;
    }

    public function getFin(): int
    {
        return min($this->total, $this->page * $this->parPage);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return \count($this->items);
    }
}
