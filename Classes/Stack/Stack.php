<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Stack;

/**
 * My own stack class because SplStack sucks
 *
 * @package Graphodata\GdPdfimport\Stack
 */
final class Stack
{
    /**
     * @var array
     */
    private $items = [];

    public function push($item): void
    {
        array_push($this->items, $item);
    }

    public function pop()
    {
        return array_pop($this->items);
    }

    public function top()
    {
        return array_reverse($this->items)[0];
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function clear(): array
    {
        $items = $this->items;
        $this->items = [];
        return $items;
    }

    public function iterateUp(): \Generator
    {
        foreach ($this->items as $item)
            yield $item;
    }

    public function iterateDown(): \Generator
    {
        foreach (array_reverse($this->items) as $item)
            yield $item;
    }
}