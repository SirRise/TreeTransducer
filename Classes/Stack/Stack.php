<?php

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

    public function getItems()
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}