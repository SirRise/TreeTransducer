<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Parser;

final class Traverser
{
    public const ENTER = 'enter';
    public const LEAVE = 'leave';

    /**
     * @param \DOMNode $node
     * @return \Generator
     */
    public static function traverse(\DOMNode $node): \Generator
    {
        yield [self::ENTER, $node];

        if ($node->hasChildNodes())
            foreach ($node->childNodes as $child)
                yield from self::traverse($child);

        yield [self::LEAVE, $node];
    }
}